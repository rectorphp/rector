<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\FileSystem;

use Nette\Application\UI\Control;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeTraverser;
use PHPStan\Type\ObjectType;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Nette to Symfony
 */
final class DeleteFactoryInterfaceRector extends AbstractFileSystemRector
{
    /**
     * @var ReturnTypeInferer
     */
    private $returnTypeInferer;

    public function __construct(ReturnTypeInferer $returnTypeInferer)
    {
        $this->returnTypeInferer = $returnTypeInferer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Interface factories are not needed in Symfony. Clear constructor injection is used instead'
        );
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        /** @var Interface_|null $interface */
        $interface = $this->betterNodeFinder->findFirstInstanceOf($nodes, Interface_::class);
        if ($interface === null) {
            return;
        }

        if (! $this->isComponentFactoryInterface($interface)) {
            return;
        }

        $this->removeFile($smartFileInfo);
    }

    private function isComponentFactoryInterface(Interface_ $interface): bool
    {
        $isComponentFactoryInteface = false;

        $this->traverseNodesWithCallable($interface->stmts, function (Node $node) use (
            &$isComponentFactoryInteface
        ): int {
            if (! $node instanceof ClassMethod) {
                return NodeTraverser::STOP_TRAVERSAL;
            }

            $returnType = $this->returnTypeInferer->inferFunctionLike($node);
            if (! $returnType instanceof ObjectType) {
                return NodeTraverser::STOP_TRAVERSAL;
            }

            if (! is_a($returnType->getClassName(), Control::class, true)) {
                return NodeTraverser::STOP_TRAVERSAL;
            }

            $isComponentFactoryInteface = true;
            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $isComponentFactoryInteface;
    }
}
