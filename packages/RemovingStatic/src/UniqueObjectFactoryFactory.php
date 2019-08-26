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
use Rector\Exception\ShouldNotHappenException;
use Rector\Naming\PropertyNaming;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
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

    public function __construct(
        NameResolver $nameResolver,
        BuilderFactory $builderFactory,
        PropertyNaming $propertyNaming,
        DocBlockManipulator $docBlockManipulator
    ) {
        $this->nameResolver = $nameResolver;
        $this->builderFactory = $builderFactory;
        $this->propertyNaming = $propertyNaming;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    /**
     * @param mixed[] $staticTypesInClass
     */
    public function createFactoryClass(Class_ $class, array $staticTypesInClass): Class_
    {
        $className = $this->nameResolver->getName($class);
        $name = $className . 'Factory';

        $shortName = $this->resolveClassShortName($name);

        $factoryClassBuilder = $this->builderFactory->class($shortName)
            ->makeFinal();

        $properties = $this->createPropertiesFromTypes($staticTypesInClass);
        $factoryClassBuilder->addStmts($properties);

        // constructor
        $constructMethod = $this->createConstructMethod($staticTypesInClass);
        $factoryClassBuilder->addStmt($constructMethod);

        if ($className === null) {
            throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
        }

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

    /**
     * @param string[] $types
     */
    private function createConstructMethod(array $types): ClassMethod
    {
        $params = [];
        foreach ($types as $type) {
            $propertyName = $this->propertyNaming->fqnToVariableName($type);
            $params[] = $this->builderFactory->param($propertyName)
                ->setType(new FullyQualified($type))
                ->getNode();
        }

        $assigns = $this->createAssignsFromParams($params);

        // add assigns
        return $this->builderFactory->method('__construct')
            ->makePublic()
            ->addParams($params)
            ->addStmts($assigns)
            ->getNode();
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

        return $this->builderFactory->method('create')
            ->setReturnType(new FullyQualified($className))
            ->makePublic()
            ->addStmt($return)
            ->addParams($params)
            ->getNode();
    }

    /**
     * @param string[] $types
     *
     * @return Property[]
     */
    private function createPropertiesFromTypes(array $types): array
    {
        $properties = [];

        foreach ($types as $type) {
            $propertyName = $this->propertyNaming->fqnToVariableName($type);
            $property = $this->builderFactory->property($propertyName)
                ->makePrivate()
                ->getNode();

            // add doc block
            $this->docBlockManipulator->changeVarTag($property, '\\' . $type);
            $properties[] = $property;
        }

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
