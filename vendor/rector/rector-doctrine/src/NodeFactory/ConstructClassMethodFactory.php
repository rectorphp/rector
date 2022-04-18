<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
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
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function createFromPublicClassProperties(\PhpParser\Node\Stmt\Class_ $class) : ?\PhpParser\Node\Stmt\ClassMethod
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
        $methodBuilder = new \RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        $methodBuilder->makePublic();
        $methodBuilder->addParams($params);
        $methodBuilder->addStmts($assigns);
        return $methodBuilder->getNode();
    }
    /**
     * @return Property[]
     */
    private function resolvePublicProperties(\PhpParser\Node\Stmt\Class_ $class) : array
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
    private function createAssign(string $name) : \PhpParser\Node\Stmt\Expression
    {
        $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $name);
        $variable = new \PhpParser\Node\Expr\Variable($name);
        $assign = new \PhpParser\Node\Expr\Assign($propertyFetch, $variable);
        return new \PhpParser\Node\Stmt\Expression($assign);
    }
    private function createParam(\PhpParser\Node\Stmt\Property $property, string $propertyName) : \PhpParser\Node\Param
    {
        $propertyType = $this->nodeTypeResolver->getType($property);
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY());
        $paramVariable = new \PhpParser\Node\Expr\Variable($propertyName);
        $param = new \PhpParser\Node\Param($paramVariable);
        $param->type = $propertyTypeNode;
        return $param;
    }
}
