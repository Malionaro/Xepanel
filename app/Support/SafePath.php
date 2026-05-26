<?php

namespace App\Support;

use RuntimeException;

class SafePath
{
    public static function normalizeRoot(string $root): string
    {
        if (! is_dir($root) && ! mkdir($root, 0775, true) && ! is_dir($root)) {
            throw new RuntimeException('Could not create service directory.');
        }

        $realRoot = realpath($root);
        if (! $realRoot) {
            throw new RuntimeException('Invalid service directory.');
        }

        return rtrim($realRoot, DIRECTORY_SEPARATOR);
    }

    public static function resolve(string $root, ?string $path = '', bool $allowMissingLeaf = false): string
    {
        $realRoot = static::normalizeRoot($root);
        $path = trim((string) $path);

        if ($path === '') {
            return $realRoot;
        }

        $path = str_replace(['\\', "\0"], ['/', ''], $path);
        $parts = array_values(array_filter(explode('/', $path), fn ($part) => $part !== '' && $part !== '.'));

        foreach ($parts as $part) {
            if ($part === '..') {
                throw new RuntimeException('Path traversal is not allowed.');
            }
        }

        $candidate = $realRoot.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $parts);
        $realCandidate = realpath($candidate);

        if ($realCandidate === false && $allowMissingLeaf) {
            $parent = dirname($candidate);
            $realParent = realpath($parent);
            static::assertWithinRoot($realRoot, $realParent ?: '');

            return $candidate;
        }

        static::assertWithinRoot($realRoot, $realCandidate ?: '');

        return $realCandidate;
    }

    public static function assertWithinRoot(string $root, string $path): void
    {
        $root = rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if ($path !== $root && ! str_starts_with($path, $root)) {
            throw new RuntimeException('Resolved path escapes the service directory.');
        }
    }

    public static function cleanFilename(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?: '';
        $name = trim($name, '.');

        if ($name === '') {
            throw new RuntimeException('Invalid filename.');
        }

        return $name;
    }
}
