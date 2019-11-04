<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\SimplifyForeachToArrayFilterRector\Fixture;

class SkipIfElse
{
    public function run(array $filesAndDirectories)
    {
        foreach ($filesAndDirectories as $filesOrDirectory) {
            if (is_file($filesOrDirectory)) {
                $files[] = $filesOrDirectory;
            } else {
                $directories[] = $filesOrDirectory;
            }
        }
    }
}
