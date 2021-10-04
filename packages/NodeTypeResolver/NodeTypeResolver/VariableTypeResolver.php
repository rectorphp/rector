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
 */
final class VariableTypeResolver implements \Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface
{
    /**
     * @var string[]
     */
    private const PARENT_NODE_ATTRIBUTES = [\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE, \Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE];
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
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
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : \PHPStan\Type\Type
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
        /** @var Scope|null $nodeScope */
        $nodeScope = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if ($nodeScope !== null) {
            return $nodeScope;
        }
        return $this->resolveFromParentNodes($variable);
    }
    private function resolveFromParentNodes(\PhpParser\Node\Expr\Variable $variable) : ?\PHPStan\Analyser\Scope
    {
        foreach (self::PARENT_NODE_ATTRIBUTES as $parentNodeAttribute) {
            $parentNode = $variable->getAttribute($parentNodeAttribute);
            if (!$parentNode instanceof \PhpParser\Node) {
                continue;
            }
            $parentNodeScope = $parentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
            if (!$parentNodeScope instanceof \PHPStan\Analyser\Scope) {
                continue;
            }
            return $parentNodeScope;
        }
        return null;
    }
}
