<?php
$directory = __DIR__ . '/resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

$replacements = [
    'bg-[#0d1117]' => 'bg-gray-100 dark:bg-[#0d1117]',
    'text-[#c9d1d9]' => 'text-gray-900 dark:text-[#c9d1d9]',
    'bg-[#161b22]' => 'bg-white dark:bg-[#161b22]',
    'border-[#30363d]' => 'border-gray-300 dark:border-[#30363d]',
    'bg-[#21262d]' => 'bg-gray-200 dark:bg-[#21262d]',
    'hover:bg-[#21262d]' => 'hover:bg-gray-200 dark:hover:bg-[#21262d]',
    'hover:bg-[#30363d]' => 'hover:bg-gray-300 dark:hover:bg-[#30363d]',
    'text-white' => 'text-gray-900 dark:text-white',
    'text-gray-400' => 'text-gray-600 dark:text-gray-400',
    'text-gray-300' => 'text-gray-700 dark:text-gray-300',
    'text-gray-500' => 'text-gray-500 dark:text-gray-500', // Just in case to avoid double replace
    'divide-[#30363d]' => 'divide-gray-300 dark:divide-[#30363d]',
    'bg-black' => 'bg-gray-900 dark:bg-black', // For the console output
];

foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $content = file_get_contents($file->getPathname());
        
        foreach ($replacements as $old => $new) {
            // Only replace if it doesn't already have dark: prefix to avoid double replacement
            // Use regex to replace isolated classes
            $content = preg_replace('/\b' . preg_quote($old, '/') . '\b(?!\s*dark:)/', $new, $content);
        }
        
        file_put_contents($file->getPathname(), $content);
    }
}

echo "Refactored views for light/dark mode support.
";
