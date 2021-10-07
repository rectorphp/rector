<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Doctrine\ValueObject\AssignToPropertyFetch;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class ParamFactory
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
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
    public function createFromPropertyFetch(\PhpParser\Node\Expr\PropertyFetch $propertyFetch, array $optionalParamNames) : \PhpParser\Node\Param
    {
        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
        if ($propertyName === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $variable = new \PhpParser\Node\Expr\Variable($propertyName);
        $param = new \PhpParser\Node\Param($variable);
        $paramType = $this->nodeTypeResolver->getType($propertyFetch);
        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($paramType, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::PARAM());
        // the param is optional - make it nullable
        if (\in_array($propertyName, $optionalParamNames, \true)) {
            if (!$paramTypeNode instanceof \PhpParser\Node\UnionType && $paramTypeNode !== null && !$paramTypeNode instanceof \PhpParser\Node\NullableType) {
                $paramTypeNode = new \PhpParser\Node\NullableType($paramTypeNode);
            }
            $param->default = $this->nodeFactory->createNull();
        }
        $param->type = $paramTypeNode;
        return $param;
    }
}
