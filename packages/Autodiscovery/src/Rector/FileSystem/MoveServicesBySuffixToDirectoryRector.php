<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Rector\FileSystem;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Autodiscovery\FileMover\FileMover;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 */
final class MoveServicesBySuffixToDirectoryRector extends AbstractFileSystemRector
{
    /**
     * @var string[]
     */
    private $groupNamesBySuffix = [];

    /**
     * @var FileMover
     */
    private $fileMover;

    /**
     * @param string[] $groupNamesBySuffix
     */
    public function __construct(FileMover $fileMover, array $groupNamesBySuffix = [])
    {
        $this->groupNamesBySuffix = $groupNamesBySuffix;
        $this->fileMover = $fileMover;
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        $this->processGroupNamesBySuffix($smartFileInfo, $nodes, $this->groupNamesBySuffix);
    }

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

            // is suffix in the same category, e.g. "Exception/SomeException.php"
            $expectedLocationFilePattern = sprintf(
                '#\/%s\/.+%s#',
                preg_quote($groupName, '#'),
                preg_quote($suffixPattern, '#')
            );
            if (Strings::match($smartFileInfo->getRealPath(), $expectedLocationFilePattern)) {
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

        // nothing to move
        if ($nodesWithFileDestination === null) {
            return;
        }

        $this->removeFile($smartFileInfo);
        $this->printNodesWithFileDestination($nodesWithFileDestination);
    }
}
