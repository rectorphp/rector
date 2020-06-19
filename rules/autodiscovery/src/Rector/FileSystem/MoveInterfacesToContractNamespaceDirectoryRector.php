<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Rector\FileSystem;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Type\TypeWithClassName;
use Rector\Autodiscovery\FileMover\FileMover;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 *
 * @see \Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector\MoveInterfacesToContractNamespaceDirectoryRectorTest
 */
final class MoveInterfacesToContractNamespaceDirectoryRector extends AbstractFileSystemRector
{
    /**
     * @var FileMover
     */
    private $fileMover;

    /**
     * @var ReturnTypeInferer
     */
    private $returnTypeInferer;

    public function __construct(FileMover $fileMover, ReturnTypeInferer $returnTypeInferer)
    {
        $this->fileMover = $fileMover;
        $this->returnTypeInferer = $returnTypeInferer;
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
        /** @var Interface_|null $interface */
        $interface = $this->betterNodeFinder->findFirstInstanceOf($nodes, Interface_::class);
        if ($interface === null) {
            return;
        }

        if ($this->isNetteMagicGeneratedFactory($interface)) {
            return;
        }

        $nodesWithFileDestination = $this->fileMover->createMovedNodesAndFilePath($smartFileInfo, $nodes, 'Contract');

        // nothing to move
        if ($nodesWithFileDestination === null) {
            return;
        }

        $this->removeFile($smartFileInfo);

        $this->addClassRename(
            $nodesWithFileDestination->getOldClassName(),
            $nodesWithFileDestination->getNewClassName()
        );

        $this->printNodesWithFileDestination($nodesWithFileDestination);
    }

    /**
     * @see https://doc.nette.org/en/3.0/components#toc-components-with-dependencies
     */
    private function isNetteMagicGeneratedFactory(ClassLike $classLike): bool
    {
        foreach ($classLike->getMethods() as $classMethod) {
            $returnType = $this->returnTypeInferer->inferFunctionLike($classMethod);
            if (! $returnType instanceof TypeWithClassName) {
                continue;
            }

            $className = $returnType->getClassName();

            if (is_a($className, 'Nette\Application\UI\Control', true)) {
                return true;
            }

            if (is_a($className, 'Nette\Application\UI\Form', true)) {
                return true;
            }
        }

        return false;
    }
}
