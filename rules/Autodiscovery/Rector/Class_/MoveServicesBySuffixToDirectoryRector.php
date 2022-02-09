<?php

declare (strict_types=1);
namespace Rector\Autodiscovery\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Autodiscovery\FileLocation\ExpectedFileLocationResolver;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StringUtils;
use Rector\Core\ValueObject\Application\File;
use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
use Rector\FileSystemRector\ValueObjectFactory\AddedFileWithNodesFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20220209\Webmozart\Assert\Assert;
/**
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 *
 * @see \Rector\Tests\Autodiscovery\Rector\Class_\MoveServicesBySuffixToDirectoryRector\MoveServicesBySuffixToDirectoryRectorTest
 */
final class MoveServicesBySuffixToDirectoryRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    public const GROUP_NAMES_BY_SUFFIX = 'group_names_by_suffix';
    /**
     * @var string[]
     */
    private $groupNamesBySuffix = [];
    /**
     * @readonly
     * @var \Rector\Autodiscovery\FileLocation\ExpectedFileLocationResolver
     */
    private $expectedFileLocationResolver;
    /**
     * @readonly
     * @var \Rector\FileSystemRector\ValueObjectFactory\AddedFileWithNodesFactory
     */
    private $addedFileWithNodesFactory;
    public function __construct(\Rector\Autodiscovery\FileLocation\ExpectedFileLocationResolver $expectedFileLocationResolver, \Rector\FileSystemRector\ValueObjectFactory\AddedFileWithNodesFactory $addedFileWithNodesFactory)
    {
        $this->expectedFileLocationResolver = $expectedFileLocationResolver;
        $this->addedFileWithNodesFactory = $addedFileWithNodesFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move classes by their suffix to their own group/directory', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
// file: app/Entity/ProductRepository.php

namespace App\Entity;

class ProductRepository
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
// file: app/Repository/ProductRepository.php

namespace App\Repository;

class ProductRepository
{
}
CODE_SAMPLE
, ['Repository'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $this->processGroupNamesBySuffix($this->file->getSmartFileInfo(), $this->groupNamesBySuffix);
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $groupNamesBySuffix = $configuration[self::GROUP_NAMES_BY_SUFFIX] ?? $configuration;
        \RectorPrefix20220209\Webmozart\Assert\Assert::isArray($groupNamesBySuffix);
        \RectorPrefix20220209\Webmozart\Assert\Assert::allString($groupNamesBySuffix);
        $this->groupNamesBySuffix = $groupNamesBySuffix;
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
    private function processGroupNamesBySuffix(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, array $groupNamesBySuffix) : void
    {
        foreach ($groupNamesBySuffix as $groupNames) {
            // has class suffix
            $suffixPattern = '\\w+' . $groupNames . '(Test)?\\.php$';
            if (!\Rector\Core\Util\StringUtils::isMatch($smartFileInfo->getRealPath(), '#' . $suffixPattern . '#')) {
                continue;
            }
            if ($this->isLocatedInExpectedLocation($groupNames, $suffixPattern, $smartFileInfo)) {
                continue;
            }
            // file is already in the group
            if (\Rector\Core\Util\StringUtils::isMatch($smartFileInfo->getPath(), '#' . $groupNames . '$#')) {
                continue;
            }
            $this->moveFileToGroupName($smartFileInfo, $this->file, $groupNames);
            return;
        }
    }
    private function isLocatedInExpectedLocation(string $groupName, string $suffixPattern, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : bool
    {
        $expectedLocationFilePattern = $this->expectedFileLocationResolver->resolve($groupName, $suffixPattern);
        return \Rector\Core\Util\StringUtils::isMatch($smartFileInfo->getRealPath(), $expectedLocationFilePattern);
    }
    private function moveFileToGroupName(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo, \Rector\Core\ValueObject\Application\File $file, string $desiredGroupName) : void
    {
        $addedFileWithNodes = $this->addedFileWithNodesFactory->createWithDesiredGroup($fileInfo, $file, $desiredGroupName);
        if (!$addedFileWithNodes instanceof \Rector\FileSystemRector\ValueObject\AddedFileWithNodes) {
            return;
        }
        $this->removedAndAddedFilesCollector->removeFile($fileInfo);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithNodes);
    }
}
