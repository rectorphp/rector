<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\SimplifyForeachToArrayFilterRector\Fixture;

class Skip
{
    public function run()
    {
        $directories = [];
        $possibleDirectories = [];

        foreach ($possibleDirectories as $key => $possibleDirectory) {
            if (file_exists($key)) {
                $directories[] = $possibleDirectory;
            }
        }

        foreach ($possibleDirectories as $key => $possibleDirectory) {
            if (file_exists($possibleDirectory)) {
                $directories[] = $key;
            }
        }

        foreach ($possibleDirectories as $key => $possibleDirectory) {
            if ($this->check($possibleDirectory)) {
                $directories[] = $possibleDirectory;
            }
        }

        foreach ($possibleDirectories as $key => $possibleDirectory) {
            if (file_exists($possibleDirectory, true)) {
                $directories[] = $possibleDirectory;
            }
        }

        foreach ($possibleDirectories as $key => $possibleDirectory) {
            if (file_exists()) {
                $directories[] = $possibleDirectory;
            }
        }
    }

    private function check(string $possibleDirectory): bool
    {
        return true;
    }
}
