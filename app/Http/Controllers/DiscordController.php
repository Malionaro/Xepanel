<?php

namespace App\Http\Controllers;

use App\Models\DiscordMessage;
use App\Models\Service;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiscordController extends Controller
{
    public function webhook(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'content' => 'required|string',
            'avatar_url' => 'nullable|url',
        ]);

        DiscordMessage::add($data);

        return response()->json(['status' => 'success']);
    }

    public function handleInteraction(Request $request)
    {
        $data = $request->all();

        // 1. Handle PING (Type 1)
        if (isset($data['type']) && $data['type'] == 1) {
            return response()->json(['type' => 1]);
        }

        // 2. Handle Application Command (Type 2)
        if (isset($data['type']) && $data['type'] == 2) {
            $command = $data['data']['name'];
            $options = $data['data']['options'] ?? [];
            
            // Get user info
            $discordUser = $data['member']['user']['username'] ?? 'Unknown';

            if ($command === 'status') {
                return $this->handleStatus($discordUser);
            }

            if ($command === 'start') {
                $serviceName = $options[0]['value'] ?? null;
                return $this->handleAction($serviceName, 'start', $discordUser);
            }

            if ($command === 'stop') {
                $serviceName = $options[0]['value'] ?? null;
                return $this->handleAction($serviceName, 'stop', $discordUser);
            }
        }

        return response()->json(['type' => 4, 'data' => ['content' => 'Unknown interaction type.']]);
    }

    protected function handleStatus($discordUser)
    {
        $services = Service::all();
        $content = "📊 **Panel System Status** (Requested by @{$discordUser})\n\n";

        foreach ($services as $s) {
            $statusIcon = ($s->getStatus() === 'running') ? '🟢' : '🔴';
            $content .= "{$statusIcon} **{$s->name}** - `{$s->getStatus()}`\n";
        }

        return response()->json([
            'type' => 4,
            'data' => [
                'content' => $content,
                'embeds' => [[
                    'title' => 'Global Overview',
                    'color' => 5763719,
                    'footer' => ['text' => 'FilePanel Bot System']
                ]]
            ]
        ]);
    }

    protected function handleAction($name, $action, $discordUser)
    {
        if (!$name) {
            return response()->json(['type' => 4, 'data' => ['content' => "❌ Please provide a service name."]]);
        }

        $service = collect(Service::all())->first(fn($s) => strtolower($s->name) === strtolower($name));

        if (!$service) {
            return response()->json(['type' => 4, 'data' => ['content' => "❌ Service `{$name}` not found."]]);
        }

        if ($action === 'start') {
            $service->start();
            ActivityLog::log("Discord Action: Start", "User: @{$discordUser}, Service: {$service->name}");
            return response()->json(['type' => 4, 'data' => ['content' => "🚀 Starting service **{$service->name}**... Check the panel for logs!"]]);
        }

        if ($action === 'stop') {
            $service->stop();
            ActivityLog::log("Discord Action: Stop", "User: @{$discordUser}, Service: {$service->name}");
            return response()->json(['type' => 4, 'data' => ['content' => "🛑 Stopping service **{$service->name}**..."]]);
        }

        return response()->json(['type' => 4, 'data' => ['content' => "Unknown action."]]);
    }

    public function getMessages()
    {
        return response()->json(DiscordMessage::all());
    }
}
