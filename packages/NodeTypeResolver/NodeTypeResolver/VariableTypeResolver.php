<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver\VariableTypeResolverTest
 *
 * @implements NodeTypeResolverInterface<Variable>
 */
final class VariableTypeResolver implements \Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [\PhpParser\Node\Expr\Variable::class];
    }
    /**
     * @param Variable $node
     */
    public function resolve(\PhpParser\Node $node) : \PHPStan\Type\Type
    {
        $variableName = $this->nodeNameResolver->getName($node);
        if ($variableName === null) {
            return new \PHPStan\Type\MixedType();
        }
        $scopeType = $this->resolveTypesFromScope($node, $variableName);
        if (!$scopeType instanceof \PHPStan\Type\MixedType) {
            return $scopeType;
        }
        // get from annotation
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        return $phpDocInfo->getVarType();
    }
    private function resolveTypesFromScope(\PhpParser\Node\Expr\Variable $variable, string $variableName) : \PHPStan\Type\Type
    {
        $scope = $this->resolveNodeScope($variable);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return new \PHPStan\Type\MixedType();
        }
        if (!$scope->hasVariableType($variableName)->yes()) {
            return new \PHPStan\Type\MixedType();
        }
        // this → object type is easier to work with and consistent with the rest of the code
        return $scope->getVariableType($variableName);
    }
    private function resolveNodeScope(\PhpParser\Node\Expr\Variable $variable) : ?\PHPStan\Analyser\Scope
    {
        $scope = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if ($scope instanceof \PHPStan\Analyser\Scope) {
            return $scope;
        }
        return $this->resolveFromParentNodes($variable);
    }
    private function resolveFromParentNodes(\PhpParser\Node\Expr\Variable $variable) : ?\PHPStan\Analyser\Scope
    {
        $parent = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            return null;
        }
        return $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
    }
}
