<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class AutoUpdatePanel extends Command
{
    protected $signature = 'panel:update
        {--repo= : GitHub repository in owner/name format}
        {--branch=main : Git branch to update from}
        {--dry-run : Fetch and report update state without changing files}
        {--force : Continue even when the worktree has local changes}';

    protected $description = 'Update Xepanel from GitHub and run dependency, migration, and cache refresh steps.';

    private string $logPath;

    public function handle(): int
    {
        $this->logPath = storage_path('logs/update.log');
        File::ensureDirectoryExists(dirname($this->logPath));
        file_put_contents($this->logPath, "\n==== Xepanel update ".now()->toDateTimeString()." ====\n", FILE_APPEND);

        $repo = $this->option('repo') ?: Setting::get('github_repo', 'Malionaro/Xepanel');
        $branch = $this->option('branch') ?: 'main';

        if (! preg_match('/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/', $repo)) {
            $this->error('Invalid repository. Expected owner/name, for example Malionaro/Xepanel.');

            return self::FAILURE;
        }

        if (! $this->commandExists('git')) {
            $this->error('Git is not available in PATH.');

            return self::FAILURE;
        }

        $this->runStep(['git', 'remote', 'set-url', 'origin', "https://github.com/{$repo}.git"]);
        $this->runStep(['git', 'fetch', 'origin', $branch, '--prune']);

        $local = trim($this->runStep(['git', 'rev-parse', 'HEAD'], capture: true));
        $remote = trim($this->runStep(['git', 'rev-parse', "origin/{$branch}"], capture: true));

        $this->info("Repository: {$repo}");
        $this->info("Local:  ".substr($local, 0, 12));
        $this->info("Remote: ".substr($remote, 0, 12));

        if ($local === $remote && ! $this->option('dry-run')) {
            $this->info('Already up to date.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->line($local === $remote ? 'No update available.' : 'Update available.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $status = trim($this->runStep(['git', 'status', '--porcelain'], capture: true));
            if ($status !== '') {
                $this->error('Worktree has local changes. Commit/stash them or run with --force.');

                return self::FAILURE;
            }
        }

        $this->runStep(['git', 'pull', '--ff-only', 'origin', $branch]);

        if ($this->commandExists('composer')) {
            $this->runStep(['composer', 'install', '--no-interaction', '--prefer-dist', '--optimize-autoloader']);
        }

        if ($this->commandExists('npm') && file_exists(base_path('package.json'))) {
            $this->runStep(['npm', 'install']);
            $this->runStep(['npm', 'run', 'build']);
        }

        $php = PHP_BINARY ?: 'php';
        $this->runStep([$php, 'artisan', 'migrate', '--force']);
        $this->runStep([$php, 'artisan', 'optimize:clear']);
        $this->runStep([$php, 'artisan', 'config:cache']);

        $this->info('Update complete.');

        return self::SUCCESS;
    }

    private function runStep(array $command, bool $capture = false): string
    {
        $display = implode(' ', array_map(fn ($part) => str_contains($part, ' ') ? '"'.$part.'"' : $part, $command));
        file_put_contents($this->logPath, '$ '.$display."\n", FILE_APPEND);

        $process = new Process($command, base_path(), null, null, 600);
        $process->run(function ($type, $buffer) use ($capture) {
            file_put_contents($this->logPath, $buffer, FILE_APPEND);
            if (! $capture) {
                $this->output->write($buffer);
            }
        });

        if (! $process->isSuccessful()) {
            throw new \RuntimeException("Command failed: {$display}");
        }

        return $process->getOutput();
    }

    private function commandExists(string $command): bool
    {
        $lookup = PHP_OS_FAMILY === 'Windows' ? ['where', $command] : ['sh', '-lc', 'command -v '.escapeshellarg($command)];
        $process = new Process($lookup);
        $process->run();

        return $process->isSuccessful();
    }
}
