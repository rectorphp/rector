<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\ChangesReporting\Rector\AbstractRector\NotifyingRemovingNodeTrait;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
use Rector\PostRector\Rector\AbstractRector\NodeCommandersTrait;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

trait AbstractRectorTrait
{
    use RemovedAndAddedFilesTrait;
    use NodeCommandersTrait;
    use NotifyingRemovingNodeTrait;

    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    /**
     * @var TypeUnwrapper
     */
    protected $typeUnwrapper;

    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @var BetterStandardPrinter
     */
    protected $betterStandardPrinter;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @required
     */
    public function autowireAbstractRectorTrait(
        BetterStandardPrinter $betterStandardPrinter,
        NodeNameResolver $nodeNameResolver,
        ClassNaming $classNaming,
        NodeTypeResolver $nodeTypeResolver,
        TypeUnwrapper $typeUnwrapper,
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser
    ): void {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classNaming = $classNaming;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeUnwrapper = $typeUnwrapper;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }

    protected function isName(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isName($node, $name);
    }

    protected function areNamesEqual(Node $firstNode, Node $secondNode): bool
    {
        return $this->nodeNameResolver->areNamesEqual($firstNode, $secondNode);
    }

    /**
     * @param string[] $names
     */
    protected function isNames(Node $node, array $names): bool
    {
        return $this->nodeNameResolver->isNames($node, $names);
    }

    protected function getName(Node $node): ?string
    {
        return $this->nodeNameResolver->getName($node);
    }

    /**
     * @param string|Name|Identifier|ClassLike $name
     */
    protected function getShortName($name): string
    {
        return $this->classNaming->getShortName($name);
    }

    protected function isLocalPropertyFetchNamed(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isLocalPropertyFetchNamed($node, $name);
    }

    protected function isLocalMethodCallNamed(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isLocalMethodCallNamed($node, $name);
    }

    protected function isFuncCallName(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isFuncCallName($node, $name);
    }

    protected function isStaticCallNamed(Node $node, string $className, string $methodName): bool
    {
        return $this->nodeNameResolver->isStaticCallNamed($node, $className, $methodName);
    }

    protected function isVariableName(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isVariableName($node, $name);
    }

    /**
     * @param ObjectType|string $type
     */
    protected function isObjectType(Node $node, $type): bool
    {
        return $this->nodeTypeResolver->isObjectType($node, $type);
    }

    /**
     * @param string[]|ObjectType[] $requiredTypes
     */
    protected function isObjectTypes(Node $node, array $requiredTypes): bool
    {
        return $this->nodeTypeResolver->isObjectTypes($node, $requiredTypes);
    }

    protected function isNumberType(Node $node): bool
    {
        return $this->nodeTypeResolver->isNumberType($node);
    }

    protected function isStaticType(Node $node, string $staticTypeClass): bool
    {
        return $this->nodeTypeResolver->isStaticType($node, $staticTypeClass);
    }

    protected function getStaticType(Node $node): Type
    {
        return $this->nodeTypeResolver->getStaticType($node);
    }

    protected function isNullableType(Node $node): bool
    {
        return $this->nodeTypeResolver->isNullableType($node);
    }

    protected function getObjectType(Node $node): Type
    {
        return $this->nodeTypeResolver->resolve($node);
    }

    /**
     * @param Node|Node[] $nodes
     */
    protected function traverseNodesWithCallable($nodes, callable $callable): void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, $callable);
    }

    /**
     * @param Node|Node[]|null $node
     */
    protected function print($node): string
    {
        return $this->betterStandardPrinter->print($node);
    }

    /**
     * Removes all comments from both nodes
     *
     * @param Node|Node[]|null $firstNode
     * @param Node|Node[]|null $secondNode
     */
    protected function areNodesEqual($firstNode, $secondNode): bool
    {
        return $this->betterStandardPrinter->areNodesEqual($firstNode, $secondNode);
    }
}
