<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipDeclaredInExtraParentPrevious
{
    function myMethod()
    {
        $absoluteDirectories = [];

        foreach ($directories as $directory) {
            if (Strings::contains($directory, '*')) {
                $absoluteDirectories = array_merge($absoluteDirectories, glob($directory, GLOB_ONLYDIR));
            } else {
                $this->ensureDirectoryExists($directory);
                $absoluteDirectories[] = $directory;
            }
        }
    }
}

?>
