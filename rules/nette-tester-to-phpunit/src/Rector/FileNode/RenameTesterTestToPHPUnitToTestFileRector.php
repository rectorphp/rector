<?php

declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Rector\FileNode;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Core\PhpParser\Node\CustomNode\FileNode;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\MovedFileWithContent;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\NetteTesterToPHPUnit\Tests\Rector\FileNode\RenameTesterTestToPHPUnitToTestFileRector\RenameTesterTestToPHPUnitToTestFileRectorTest
 */
final class RenameTesterTestToPHPUnitToTestFileRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/ioamnE/1
     */
    private const PHP_SUFFIX_REGEX = '#\.php$#';

    /**
     * @var string
     * @see https://regex101.com/r/cOMZIj/1
     */
    private const PHPT_SUFFIX_REGEX = '#\.phpt$#';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename "*.phpt" file to "*Test.php" file', [
            new CodeSample(
                <<<'CODE_SAMPLE'
// tests/SomeTestCase.phpt
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
// tests/SomeTestCase.php
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FileNode::class];
    }

    /**
     * @param FileNode $node
     */
    public function refactor(Node $node): ?Node
    {
        $smartFileInfo = $node->getFileInfo();
        $oldRealPath = $smartFileInfo->getRealPath();
        if (! Strings::endsWith($oldRealPath, '.phpt')) {
            return null;
        }

        $newRealPath = $this->createNewRealPath($oldRealPath);
        if ($newRealPath === $oldRealPath) {
            return null;
        }

        $movedFileWithContent = new MovedFileWithContent($smartFileInfo, $newRealPath);
        $this->removedAndAddedFilesCollector->addMovedFile($movedFileWithContent);

        return null;
    }

    private function createNewRealPath(string $oldRealPath): string
    {
        // file suffix
        $newRealPath = Strings::replace($oldRealPath, self::PHPT_SUFFIX_REGEX, '.php');

        // Test suffix
        if (! Strings::endsWith($newRealPath, 'Test.php')) {
            return Strings::replace($newRealPath, self::PHP_SUFFIX_REGEX, 'Test.php');
        }

        return $newRealPath;
    }
}
