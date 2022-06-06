<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\ComplexType;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20220606\Rector\Doctrine\ValueObject\AssignToPropertyFetch;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use RectorPrefix20220606\Rector\StaticTypeMapper\StaticTypeMapper;
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
    public function createFromPropertyFetch(PropertyFetch $propertyFetch, array $optionalParamNames) : Param
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
                $paramTypeNode = new NullableType($paramTypeNode);
            }
            $param->default = $this->nodeFactory->createNull();
        }
        $param->type = $paramTypeNode;
        return $param;
    }
}
