<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Rector\FileNode;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Autodiscovery\FileLocation\ExpectedFileLocationResolver;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\CustomNode\FileNode;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\MovedFileWithNodes;
use Rector\FileSystemRector\ValueObjectFactory\MovedFileWithNodesFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;
use Webmozart\Assert\Assert;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 *
 * @see \Rector\Autodiscovery\Tests\Rector\FileNode\MoveServicesBySuffixToDirectoryRector\MoveServicesBySuffixToDirectoryRectorTest
 * @see \Rector\Autodiscovery\Tests\Rector\FileNode\MoveServicesBySuffixToDirectoryRector\MutualRenameTest
 */
final class MoveServicesBySuffixToDirectoryRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const GROUP_NAMES_BY_SUFFIX = 'group_names_by_suffix';

    /**
     * @var string[]
     */
    private $groupNamesBySuffix = [];

    /**
     * @var ExpectedFileLocationResolver
     */
    private $expectedFileLocationResolver;

    /**
     * @var MovedFileWithNodesFactory
     */
    private $movedFileWithNodesFactory;

    public function __construct(
        ExpectedFileLocationResolver $expectedFileLocationResolver,
        MovedFileWithNodesFactory $movedFileWithNodesFactory
    ) {
        $this->expectedFileLocationResolver = $expectedFileLocationResolver;
        $this->movedFileWithNodesFactory = $movedFileWithNodesFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move classes by their suffix to their own group/directory', [
            new ConfiguredCodeSample(
                        <<<'CODE_SAMPLE'
// file: app/Entity/ProductRepository.php

namespace App/Entity;

class ProductRepository
{
}
CODE_SAMPLE
                        ,
                        <<<'CODE_SAMPLE'
// file: app/Repository/ProductRepository.php

namespace App/Repository;

class ProductRepository
{
}
CODE_SAMPLE
                        ,
                        [
                            self::GROUP_NAMES_BY_SUFFIX => ['Repository'],
                        ]
                    ),
        ]);
    }

    /**
     * @param FileNode $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLikes = $this->betterNodeFinder->findClassLikes($node);
        if ($classLikes === []) {
            return null;
        }

        $this->processGroupNamesBySuffix($node->getFileInfo(), $node, $this->groupNamesBySuffix);

        return null;
    }

    public function configure(array $configuration): void
    {
        $groupNamesBySuffix = $configuration[self::GROUP_NAMES_BY_SUFFIX] ?? [];
        Assert::allString($groupNamesBySuffix);

        $this->groupNamesBySuffix = $groupNamesBySuffix;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FileNode::class];
    }

    /**
     * A. Match classes by suffix and move them to group namespace
     *
     * E.g. "App\Controller\SomeException"
     * ↓
     * "App\Exception\SomeException"
     *
     * @param string[] $groupNamesBySuffix
     */
    private function processGroupNamesBySuffix(
        SmartFileInfo $smartFileInfo,
        FileNode $fileNode,
        array $groupNamesBySuffix
    ): void {
        foreach ($groupNamesBySuffix as $groupName) {
            // has class suffix
            $suffixPattern = '\w+' . $groupName . '(Test)?\.php$';
            if (! Strings::match($smartFileInfo->getRealPath(), '#' . $suffixPattern . '#')) {
                continue;
            }

            if ($this->isLocatedInExpectedLocation($groupName, $suffixPattern, $smartFileInfo)) {
                continue;
            }

            // file is already in the group
            if (Strings::match($smartFileInfo->getPath(), '#' . $groupName . '$#')) {
                continue;
            }

            $this->moveFileToGroupName($smartFileInfo, $fileNode, $groupName);
            return;
        }
    }

    private function isLocatedInExpectedLocation(
        string $groupName,
        string $suffixPattern,
        SmartFileInfo $smartFileInfo
    ): bool {
        $expectedLocationFilePattern = $this->expectedFileLocationResolver->resolve($groupName, $suffixPattern);

        return (bool) Strings::match($smartFileInfo->getRealPath(), $expectedLocationFilePattern);
    }

    private function moveFileToGroupName(SmartFileInfo $fileInfo, FileNode $fileNode, string $desiredGroupName): void
    {
        $movedFileWithNodes = $this->movedFileWithNodesFactory->createWithDesiredGroup(
            $fileInfo,
            $fileNode->stmts,
            $desiredGroupName
        );
        if (! $movedFileWithNodes instanceof MovedFileWithNodes) {
            return;
        }

        $this->removedAndAddedFilesCollector->addMovedFile($movedFileWithNodes);
    }
}
