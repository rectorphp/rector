<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Rector\FileSystem;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileMovingFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 *
 * @see \Rector\Autodiscovery\Tests\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector\MoveServicesBySuffixToDirectoryRectorTest
 * @see \Rector\Autodiscovery\Tests\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector\MutualRenameTest
 */
final class MoveServicesBySuffixToDirectoryRector extends AbstractFileMovingFileSystemRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const GROUP_NAMES_BY_SUFFIX = '$groupNamesBySuffix';

    /**
     * @var string[]
     */
    private $groupNamesBySuffix = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move classes by their suffix to their own group/directory', [new ConfiguredCodeSample(
            <<<'PHP'
// file: app/Entity/ProductRepository.php

namespace App/Entity;

class ProductRepository
{
}
PHP
            ,
            <<<'PHP'
// file: app/Repository/ProductRepository.php

namespace App/Repository;

class ProductRepository
{
}
PHP
            ,
            [
                '$groupNamesBySuffix' => ['Repository'],
            ]
        )]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        $class = $this->betterNodeFinder->findFirstInstanceOf($nodes, Class_::class);
        if ($class === null) {
            return;
        }

        $this->processGroupNamesBySuffix($smartFileInfo, $nodes, $this->groupNamesBySuffix);
    }

    public function configure(array $configuration): void
    {
        $this->groupNamesBySuffix = $configuration[self::GROUP_NAMES_BY_SUFFIX] ?? [];
    }

    /**
     * A. Match classes by suffix and move them to group namespace
     * E.g. App\Controller\SomeException → App\Exception\SomeException
     *
     * @param Node[] $nodes
     * @param string[] $groupNamesBySuffix
     */
    private function processGroupNamesBySuffix(
        SmartFileInfo $smartFileInfo,
        array $nodes,
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

            $this->moveFileToGroupName($smartFileInfo, $nodes, $groupName);
            return;
        }
    }

    /**
     * @param Node[] $nodes
     */
    private function moveFileToGroupName(SmartFileInfo $smartFileInfo, array $nodes, string $desiredGroupName): void
    {
        $nodesWithFileDestination = $this->fileMover->createMovedNodesAndFilePath(
            $smartFileInfo,
            $nodes,
            $desiredGroupName
        );

        $this->processNodesWithFileDestination($nodesWithFileDestination);
    }

    /**
     * Checks if is suffix in the same category, e.g. "Exception/SomeException.php"
     */
    private function createExpectedFileLocationPattern(string $groupName, string $suffixPattern): string
    {
        return sprintf('#\/%s\/.+%s#', preg_quote($groupName, '#'), preg_quote($suffixPattern, '#'));
    }

    private function isLocatedInExpectedLocation(
        string $groupName,
        string $suffixPattern,
        SmartFileInfo $smartFileInfo
    ): bool {
        $expectedLocationFilePattern = $this->createExpectedFileLocationPattern($groupName, $suffixPattern);

        return (bool) Strings::match($smartFileInfo->getRealPath(), $expectedLocationFilePattern);
    }
}
