<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\FileNode;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Core\PhpParser\Node\CustomNode\FileNode;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\MovedFileWithContent;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://gnugat.github.io/2015/09/23/phpunit-with-phpspec.html
 *
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\FileNode\RenameSpecFileToTestFileRector\RenameSpecFileToTestFileRectorTest
 */
final class RenameSpecFileToTestFileRector extends AbstractRector
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

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rename "*Spec.php" file to "*Test.php" file',
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
        $fileInfo = $node->getFileInfo();
        $oldPathname = $fileInfo->getPathname();

        // ends with Spec.php
        if (! Strings::match($oldPathname, self::SPEC_SUFFIX_REGEX)) {
            return null;
        }

        $newPathName = $this->createPathName($oldPathname);
        $movedFileWithContent = new MovedFileWithContent($fileInfo, $newPathName);
        $this->removedAndAddedFilesCollector->addMovedFile($movedFileWithContent);

        return null;
    }

    private function createPathName(string $oldRealPath): string
    {
        // suffix
        $newRealPath = Strings::replace($oldRealPath, self::SPEC_SUFFIX_REGEX, 'Test.php');

        // directory
        return Strings::replace($newRealPath, self::SPEC_REGEX, '/tests/');
    }
}
