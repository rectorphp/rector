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
use RectorPrefix20220609\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix20220609\Symplify\PackageBuilder\Parameter\ParameterProvider;
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
    public function __construct(ParameterProvider $parameterProvider, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider)
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
        if ($this->parameterProvider->provideBoolParameter(Option::AUTO_IMPORT_NAMES)) {
            return;
        }
        // FQNize all class names
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) : ?FullyQualified {
            if (!$node instanceof Name) {
                return null;
            }
            $name = $this->nodeNameResolver->getName($node);
            if (\in_array($name, [ObjectReference::STATIC, ObjectReference::PARENT, ObjectReference::SELF], \true)) {
                return null;
            }
            if ($this->isNativeConstant($node)) {
                return null;
            }
            return new FullyQualified($name);
        });
    }
    private function isNativeConstant(Name $name) : bool
    {
        $parent = $name->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parent instanceof ConstFetch) {
            return \false;
        }
        $scope = $name->getAttribute(AttributeKey::SCOPE);
        if (!$this->reflectionProvider->hasConstant($name, $scope)) {
            return \false;
        }
        $constantReflection = $this->reflectionProvider->getConstant($name, $scope);
        return $constantReflection instanceof RuntimeConstantReflection;
    }
}
