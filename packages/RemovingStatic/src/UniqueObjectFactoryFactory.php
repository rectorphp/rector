<?php

declare(strict_types=1);

namespace Rector\RemovingStatic;

use Nette\Utils\Strings;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Exception\ShouldNotHappenException;
use Rector\Naming\PropertyNaming;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\NodeTypeResolver\StaticTypeMapper;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class UniqueObjectFactoryFactory
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    public function __construct(
        NameResolver $nameResolver,
        BuilderFactory $builderFactory,
        PropertyNaming $propertyNaming,
        DocBlockManipulator $docBlockManipulator,
        StaticTypeMapper $staticTypeMapper
    ) {
        $this->nameResolver = $nameResolver;
        $this->builderFactory = $builderFactory;
        $this->propertyNaming = $propertyNaming;
        $this->docBlockManipulator = $docBlockManipulator;
        $this->staticTypeMapper = $staticTypeMapper;
    }

    public function createFactoryClass(Class_ $class, ObjectType $objectType): Class_
    {
        $className = $this->nameResolver->getName($class);
        if ($className === null) {
            throw new ShouldNotHappenException();
        }

        $name = $className . 'Factory';

        $shortName = $this->resolveClassShortName($name);

        $factoryClassBuilder = $this->builderFactory->class($shortName);
        $factoryClassBuilder->makeFinal();

        $properties = $this->createPropertiesFromTypes($objectType);
        $factoryClassBuilder->addStmts($properties);

        // constructor
        $constructMethod = $this->createConstructMethod($objectType);
        $factoryClassBuilder->addStmt($constructMethod);

        // create
        $createMethod = $this->createCreateMethod($class, $className, $properties);
        $factoryClassBuilder->addStmt($createMethod);

        return $factoryClassBuilder->getNode();
    }

    /**
     * @param Param[] $params
     *
     * @return Assign[]
     */
    private function createAssignsFromParams(array $params): array
    {
        $assigns = [];

        /** @var Param $param */
        foreach ($params as $param) {
            $propertyFetch = new PropertyFetch(new Variable('this'), $param->var->name);
            $assigns[] = new Assign($propertyFetch, new Variable($param->var->name));
        }

        return $assigns;
    }

    private function createConstructMethod(ObjectType $objectType): ClassMethod
    {
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $paramBuilder = $this->builderFactory->param($propertyName);

        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($objectType);
        if ($typeNode) {
            $paramBuilder->setType($typeNode);
        }

        $params = [$paramBuilder->getNode()];

        $assigns = $this->createAssignsFromParams($params);

        $methodBuilder = $this->builderFactory->method('__construct');
        $methodBuilder->makePublic();
        $methodBuilder->addParams($params);
        $methodBuilder->addStmts($assigns);

        return $methodBuilder->getNode();
    }

    /**
     * @param Property[] $properties
     */
    private function createCreateMethod(Class_ $class, string $className, array $properties): ClassMethod
    {
        $new = new New_(new FullyQualified($className));

        $constructClassMethod = $class->getMethod('__construct');
        $params = [];
        if ($constructClassMethod !== null) {
            foreach ($constructClassMethod->params as $param) {
                $params[] = $param;
                $new->args[] = new Arg($param->var);
            }
        }

        foreach ($properties as $property) {
            $propertyFetch = new PropertyFetch(new Variable('this'), $this->nameResolver->getName($property));
            $new->args[] = new Arg($propertyFetch);
        }

        $return = new Return_($new);

        $builderMethod = $this->builderFactory->method('create');
        $builderMethod->setReturnType(new FullyQualified($className));
        $builderMethod->makePublic();
        $builderMethod->addStmt($return);
        $builderMethod->addParams($params);

        return $builderMethod->getNode();
    }

    /**
     * @return Property[]
     */
    private function createPropertiesFromTypes(ObjectType $objectType): array
    {
        $properties = [];

        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $propertyBuilder = $this->builderFactory->property($propertyName);
        $propertyBuilder->makePrivate();

        $property = $propertyBuilder->getNode();

        $this->docBlockManipulator->changeVarTag($property, $objectType);

        $properties[] = $property;

        return $properties;
    }

    private function resolveClassShortName(string $name): string
    {
        if (Strings::contains($name, '\\')) {
            return (string) Strings::after($name, '\\', -1);
        }

        return $name;
    }
}
