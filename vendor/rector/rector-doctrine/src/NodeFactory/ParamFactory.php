<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Doctrine\ValueObject\AssignToPropertyFetch;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class ParamFactory
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeTypeResolver $nodeTypeResolver, StaticTypeMapper $staticTypeMapper, NodeNameResolver $nodeNameResolver, NodeFactory $nodeFactory)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param AssignToPropertyFetch[] $assignsToPropertyFetch
     * @param string[] $optionalParamNames
     * @return Param[]
     */
    public function createFromAssignsToPropertyFetch(array $assignsToPropertyFetch, array $optionalParamNames) : array
    {
        $params = [];
        foreach ($assignsToPropertyFetch as $assignToPropertyFetch) {
            $propertyFetch = $assignToPropertyFetch->getPropertyFetch();
            $params[] = $this->createFromPropertyFetch($propertyFetch, $optionalParamNames);
        }
        return $params;
    }
    /**
     * @param string[] $optionalParamNames
     */
    private function createFromPropertyFetch(PropertyFetch $propertyFetch, array $optionalParamNames) : Param
    {
        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
        if ($propertyName === null) {
            throw new ShouldNotHappenException();
        }
        $variable = new Variable($propertyName);
        $param = new Param($variable);
        $paramType = $this->nodeTypeResolver->getType($propertyFetch);
        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($paramType, TypeKind::PARAM);
        // the param is optional - make it nullable
        if (\in_array($propertyName, $optionalParamNames, \true)) {
            if (!$paramTypeNode instanceof ComplexType && $paramTypeNode !== null) {
                $param->type = new NullableType($paramTypeNode);
            }
            $param->default = $this->nodeFactory->createNull();
        }
        if ($param->type === null) {
            $param->type = $paramTypeNode;
        }
        return $param;
    }
}
