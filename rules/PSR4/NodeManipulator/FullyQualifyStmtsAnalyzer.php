<?php

declare(strict_types=1);

namespace Rector\PSR4\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PHPStan\Reflection\Constant\RuntimeConstantReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Configuration\Option;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class FullyQualifyStmtsAnalyzer
{
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(
        ParameterProvider $parameterProvider,
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        ReflectionProvider $reflectionProvider
    ) {
        $this->parameterProvider = $parameterProvider;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }

    /**
     * @param Stmt[] $nodes
     */
    public function process(array $nodes): void
    {
        // no need to
        if ($this->parameterProvider->provideBoolParameter(Option::AUTO_IMPORT_NAMES)) {
            return;
        }

        // FQNize all class names
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node): ?FullyQualified {
            if (! $node instanceof Name) {
                return null;
            }

            $fullyQualifiedName = $this->nodeNameResolver->getName($node);
            if (in_array($fullyQualifiedName, ['self', 'parent', 'static'], true)) {
                return null;
            }

            if ($this->isNativeConstant($node)) {
                return null;
            }

            return new FullyQualified($fullyQualifiedName);
        });
    }

    private function isNativeConstant(Name $name): bool
    {
        $parent = $name->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof ConstFetch) {
            return false;
        }

        $scope = $name->getAttribute(AttributeKey::SCOPE);
        if (! $this->reflectionProvider->hasConstant($name, $scope)) {
            return false;
        }

        $constantReflection = $this->reflectionProvider->getConstant($name, $scope);
        return $constantReflection instanceof RuntimeConstantReflection;
    }
}
