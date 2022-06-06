<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use RectorPrefix20220606\Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220606\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
final class ConstructClassMethodFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
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
    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, StaticTypeMapper $staticTypeMapper)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function createFromPublicClassProperties(Class_ $class) : ?ClassMethod
    {
        $publicProperties = $this->resolvePublicProperties($class);
        if ($publicProperties === []) {
            return null;
        }
        $params = [];
        $assigns = [];
        foreach ($publicProperties as $publicProperty) {
            /** @var string $propertyName */
            $propertyName = $this->nodeNameResolver->getName($publicProperty);
            $params[] = $this->createParam($publicProperty, $propertyName);
            $assigns[] = $this->createAssign($propertyName);
        }
        $methodBuilder = new MethodBuilder(MethodName::CONSTRUCT);
        $methodBuilder->makePublic();
        $methodBuilder->addParams($params);
        $methodBuilder->addStmts($assigns);
        return $methodBuilder->getNode();
    }
    /**
     * @return Property[]
     */
    private function resolvePublicProperties(Class_ $class) : array
    {
        $publicProperties = [];
        foreach ($class->getProperties() as $property) {
            if (!$property->isPublic()) {
                continue;
            }
            $publicProperties[] = $property;
        }
        return $publicProperties;
    }
    private function createAssign(string $name) : Expression
    {
        $propertyFetch = new PropertyFetch(new Variable('this'), $name);
        $variable = new Variable($name);
        $assign = new Assign($propertyFetch, $variable);
        return new Expression($assign);
    }
    private function createParam(Property $property, string $propertyName) : Param
    {
        $propertyType = $this->nodeTypeResolver->getType($property);
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
        $paramVariable = new Variable($propertyName);
        $param = new Param($paramVariable);
        $param->type = $propertyTypeNode;
        return $param;
    }
}
