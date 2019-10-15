<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Rector\FileSystem;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use Rector\Autodiscovery\FileMover\FileMover;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 */
final class MoveInterfacesToContractNamespaceDirectoryRector extends AbstractFileSystemRector
{
    /**
     * @var FileMover
     */
    private $fileMover;

    public function __construct(FileMover $fileMover)
    {
        $this->fileMover = $fileMover;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move interface to "Contract" namespace', [new CodeSample(
<<<'PHP'
// file: app/Exception/Rule.php

namespace App\Exception;

interface Rule
{
} 
PHP
            ,
            <<<'PHP'
// file: app/Contract/Rule.php

namespace App\Contract;

interface Rule
{
}
PHP
        )]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        $this->processInterfacesToContract($smartFileInfo, $nodes);
    }

    /**
     * @param Node[] $nodes
     */
    private function processInterfacesToContract(SmartFileInfo $smartFileInfo, array $nodes): void
    {
        $interfaceNode = $this->betterNodeFinder->findFirstInstanceOf($nodes, Interface_::class);
        if ($interfaceNode === null) {
            return;
        }

        $nodesWithFileDestination = $this->fileMover->createMovedNodesAndFilePath($smartFileInfo, $nodes, 'Contract');

        // nothing to move
        if ($nodesWithFileDestination === null) {
            return;
        }

        $this->removeFile($smartFileInfo);

        $this->printNodesWithFileDestination($nodesWithFileDestination);
    }
}
