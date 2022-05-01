<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20220501\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class MockedVariableAnalyzer
{
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\RectorPrefix20220501\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function containsMockAsUsedVariable(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $doesContainMock = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (\PhpParser\Node $node) use(&$doesContainMock) {
            if (!$node instanceof \PhpParser\Node\Expr\PropertyFetch && !$node instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            $variableType = $this->nodeTypeResolver->getType($node);
            if ($variableType instanceof \PHPStan\Type\MixedType) {
                return null;
            }
            if ($variableType->isSuperTypeOf(new \PHPStan\Type\ObjectType('PHPUnit\\Framework\\MockObject\\MockObject'))->yes()) {
                $doesContainMock = \true;
            }
            return null;
        });
        return $doesContainMock;
    }
}
