<?php

declare(strict_types=1);

/**
 * Convert leading indentation of changed PHP files from 4 spaces to 2 spaces.
 * Intended to run after Pint.
 */
$command = "git diff --name-only --diff-filter=ACMRTUXB HEAD -- '*.php'";
$output = shell_exec($command);

if (! is_string($output)) {
  fwrite(STDERR, "Failed to read changed PHP files.\n");
  exit(1);
}

$files = array_values(array_filter(array_map('trim', explode("\n", $output))));

if ($files === []) {
  fwrite(STDOUT, "No changed PHP files.\n");
  exit(0);
}

$failed = false;

foreach ($files as $path) {
  if (! file_exists($path) || ! is_file($path)) {
    continue;
  }

  $content = file_get_contents($path);
  if (! is_string($content)) {
    fwrite(STDERR, "Failed to read: {$path}\n");
    $failed = true;
    continue;
  }

  $converted = preg_replace_callback(
    '/^( +)/m',
    static function (array $matches): string {
      $count = strlen($matches[1]);
      if ($count % 4 !== 0) {
        return $matches[1];
      }
      return str_repeat(' ', intdiv($count, 2));
    },
    $content
  );

  if (! is_string($converted)) {
    fwrite(STDERR, "Failed to convert: {$path}\n");
    $failed = true;
    continue;
  }

  if ($converted !== $content) {
    file_put_contents($path, $converted);
    fwrite(STDOUT, "Reindented: {$path}\n");
  }
}

exit($failed ? 1 : 0);
