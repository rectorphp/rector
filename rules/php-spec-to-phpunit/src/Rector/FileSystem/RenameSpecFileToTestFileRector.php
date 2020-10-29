<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\FileSystem;

use Nette\Utils\Strings;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see https://gnugat.github.io/2015/09/23/phpunit-with-phpspec.html
 *
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class RenameSpecFileToTestFileRector extends AbstractFileSystemRector
{
    /**
     * @var string
     * @see https://regex101.com/r/r1VkPt/1
     */
    private const SPEC_REGEX = '#\/spec\/#';

    /**
     * @var string
     * @see https://regex101.com/r/WD4U43/1
     */
    private const SPEC_SUFFIX_REGEX = '#Spec\.php$#';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename "*Spec.php" file to "*Test.php" file',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
// tests/SomeSpec.php
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
// tests/SomeTest.php
CODE_SAMPLE
                ),
            ]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $oldRealPath = $smartFileInfo->getRealPath();

        // ends with Spec.php
        if (! Strings::endsWith($oldRealPath, 'Spec.php')) {
            return;
        }

        $newRealPath = $this->createNewRealPath($oldRealPath);

        // rename
        $this->smartFileSystem->rename($oldRealPath, $newRealPath);

        // remove old file
        $this->removeFile($smartFileInfo);
    }

    private function createNewRealPath(string $oldRealPath): string
    {
        // suffix
        $newRealPath = Strings::replace($oldRealPath, self::SPEC_SUFFIX_REGEX, 'Test.php');

        // directory
        return Strings::replace($newRealPath, self::SPEC_REGEX, '/tests/');
    }
}
