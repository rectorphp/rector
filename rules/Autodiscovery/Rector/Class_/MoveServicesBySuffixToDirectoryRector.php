<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Autodiscovery\FileLocation\ExpectedFileLocationResolver;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\Application\File;
use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
use Rector\FileSystemRector\ValueObjectFactory\AddedFileWithNodesFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;
use Webmozart\Assert\Assert;

/**
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 *
 * @see \Rector\Tests\Autodiscovery\Rector\Class_\MoveServicesBySuffixToDirectoryRector\MoveServicesBySuffixToDirectoryRectorTest
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
    private array $groupNamesBySuffix = [];

    public function __construct(
        private ExpectedFileLocationResolver $expectedFileLocationResolver,
        private AddedFileWithNodesFactory $addedFileWithNodesFactory
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move classes by their suffix to their own group/directory', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
// file: app/Entity/ProductRepository.php

namespace App\Entity;

class ProductRepository
{
}
CODE_SAMPLE
                        ,
                <<<'CODE_SAMPLE'
// file: app/Repository/ProductRepository.php

namespace App\Repository;

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
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->processGroupNamesBySuffix($this->file->getSmartFileInfo(), $this->file, $this->groupNamesBySuffix);

        return null;
    }

    /**
     * @param array<string, string[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $groupNamesBySuffix = $configuration[self::GROUP_NAMES_BY_SUFFIX] ?? [];
        Assert::allString($groupNamesBySuffix);

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
    private function processGroupNamesBySuffix(
        SmartFileInfo $smartFileInfo,
        File $file,
        array $groupNamesBySuffix
    ): void {
        foreach ($groupNamesBySuffix as $groupNames) {
            // has class suffix
            $suffixPattern = '\w+' . $groupNames . '(Test)?\.php$';
            if (! Strings::match($smartFileInfo->getRealPath(), '#' . $suffixPattern . '#')) {
                continue;
            }

            if ($this->isLocatedInExpectedLocation($groupNames, $suffixPattern, $smartFileInfo)) {
                continue;
            }

            // file is already in the group
            if (Strings::match($smartFileInfo->getPath(), '#' . $groupNames . '$#')) {
                continue;
            }

            $this->moveFileToGroupName($smartFileInfo, $this->file, $groupNames);
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

    private function moveFileToGroupName(SmartFileInfo $fileInfo, File $file, string $desiredGroupName): void
    {
        $addedFileWithNodes = $this->addedFileWithNodesFactory->createWithDesiredGroup(
            $fileInfo,
            $file,
            $desiredGroupName
        );

        if (! $addedFileWithNodes instanceof AddedFileWithNodes) {
            return;
        }

        $this->removedAndAddedFilesCollector->removeFile($fileInfo);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithNodes);
    }
}
