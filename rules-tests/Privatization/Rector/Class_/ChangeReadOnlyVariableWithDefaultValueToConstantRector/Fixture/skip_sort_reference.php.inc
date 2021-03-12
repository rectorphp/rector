<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipSortReference
{
    public function testMultipleSuffixes(): void
    {
        $foundFiles = $this->filesFinder->findInDirectoriesAndFiles([__DIR__ . '/FilesFinderSource'], ['yaml', 'yml']);
        $this->assertCount(2, $foundFiles);

        $foundFileNames = [];
        foreach ($foundFiles as $foundFile) {
            $foundFileNames[] = $foundFile->getFilename();
        }

        $expectedFoundFileNames = ['some_config.yml', 'other_config.yaml'];

        sort($foundFileNames);
        sort($expectedFoundFileNames);
        $this->assertSame($expectedFoundFileNames, $foundFileNames);
    }
}
