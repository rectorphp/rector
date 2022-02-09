<?php

declare (strict_types=1);
namespace Rector\PSR4\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PHPStan\Reflection\Constant\RuntimeConstantReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Configuration\Option;
use Rector\Core\Enum\ObjectReference;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220209\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix20220209\Symplify\PackageBuilder\Parameter\ParameterProvider;
final class FullyQualifyStmtsAnalyzer
{
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\RectorPrefix20220209\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \RectorPrefix20220209\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->parameterProvider = $parameterProvider;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param Stmt[] $stmts
     */
    public function process(array $stmts) : void
    {
        // no need to
        if ($this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES)) {
            return;
        }
        // FQNize all class names
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (\PhpParser\Node $node) : ?FullyQualified {
            if (!$node instanceof \PhpParser\Node\Name) {
                return null;
            }
            $name = $this->nodeNameResolver->getName($node);
            if (\Rector\Core\Enum\ObjectReference::isValid($name)) {
                return null;
            }
            if ($this->isNativeConstant($node)) {
                return null;
            }
            return new \PhpParser\Node\Name\FullyQualified($name);
        });
    }
    private function isNativeConstant(\PhpParser\Node\Name $name) : bool
    {
        $parent = $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Expr\ConstFetch) {
            return \false;
        }
        $scope = $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$this->reflectionProvider->hasConstant($name, $scope)) {
            return \false;
        }
        $constantReflection = $this->reflectionProvider->getConstant($name, $scope);
        return $constantReflection instanceof \PHPStan\Reflection\Constant\RuntimeConstantReflection;
    }
}
