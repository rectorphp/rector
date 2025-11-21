<?php

declare(strict_types=1);

namespace Rector\FileSystem;

use function array_filter;
use function explode;
use function is_file;
use function strlen;
use function substr;
use function trim;

final class GitDirtyFileFetcher
{
    /**
     * A callable that accepts a command string and returns an array of output lines,
     * or a string. By default null -> uses exec().
     *
     * @var callable|null
     */
    private $commandRunner;

    /**
     * @param callable|null $commandRunner signature: fn(string $cmd): array|string
     */
    public function __construct(?callable $commandRunner = null)
    {
        $this->commandRunner = $commandRunner;
    }

    /**
     * Return relative file paths reported by `git status --porcelain`.
     *
     * - includes modified (M), added (A), untracked (??) files
     * - strips the two-character status prefix and whitespace
     *
     * @return string[] relative paths
     */
    public function fetchDirtyFiles(): array
    {
        $lines = $this->runCommand('git status --porcelain');

        $files = [];

        foreach ($lines as $line) {
            $line = (string)$line;
            if (trim($line) === '') {
                continue;
            }

            // porcelain format: two-char status  space  path (path can include spaces)
            // e.g. " M src/Service/Foo.php", "?? newfile.php"
            if (strlen($line) <= 3) {
                continue;
            }

            $path = trim(substr($line, 3));

            if ($path === '') {
                continue;
            }

            // prefer files on disk to avoid passing directories
            if (is_file($path)) {
                $files[] = $path;
            }
        }

        // remove duplicates and empty entries
        $files = array_filter($files);

        return array_values($files);
    }

    /**
     * @return string[] lines
     */
    private function runCommand(string $command): array
    {
        if ($this->commandRunner !== null) {
            $result = ($this->commandRunner)($command);
            if (is_array($result)) {
                return $result;
            }
            if (is_string($result)) {
                return explode("\n", $result);
            }
            return [];
        }

        $output = [];
        @exec($command, $output);
        return $output;
    }
}