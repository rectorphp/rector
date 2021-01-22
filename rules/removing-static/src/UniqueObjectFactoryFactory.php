<?php

declare(strict_types=1);

namespace Rector\RemovingStatic;

use Nette\Utils\Strings;
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
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder;
use Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;

final class UniqueObjectFactoryFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(
        NodeFactory $nodeFactory,
        NodeNameResolver $nodeNameResolver,
        PropertyNaming $propertyNaming,
        StaticTypeMapper $staticTypeMapper,
        PhpDocTypeChanger $phpDocTypeChanger,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyNaming = $propertyNaming;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->nodeFactory = $nodeFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function createFactoryClass(Class_ $class, ObjectType $objectType): Class_
    {
        $className = $this->nodeNameResolver->getName($class);
        if ($className === null) {
            throw new ShouldNotHappenException();
        }

        $name = $className . 'Factory';

        $shortName = $this->resolveClassShortName($name);

        $factoryClassBuilder = new ClassBuilder($shortName);
        $factoryClassBuilder->makeFinal();

        $properties = $this->createPropertiesFromTypes($objectType);
        $factoryClassBuilder->addStmts($properties);

        // constructor
        $constructorClassMethod = $this->createConstructMethod($objectType);
        $factoryClassBuilder->addStmt($constructorClassMethod);

        // create
        $classMethod = $this->createCreateMethod($class, $className, $properties);
        $factoryClassBuilder->addStmt($classMethod);

        return $factoryClassBuilder->getNode();
    }

    private function resolveClassShortName(string $name): string
    {
        if (Strings::contains($name, '\\')) {
            return (string) Strings::after($name, '\\', -1);
        }

        return $name;
    }

    /**
     * @return Property[]
     */
    private function createPropertiesFromTypes(ObjectType $objectType): array
    {
        $properties = [];
        $properties[] = $this->createPropertyFromObjectType($objectType);

        return $properties;
    }

    private function createConstructMethod(ObjectType $objectType): ClassMethod
    {
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $paramBuilder = new ParamBuilder($propertyName);

        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($objectType);
        if ($typeNode !== null) {
            $paramBuilder->setType($typeNode);
        }

        $params = [$paramBuilder->getNode()];

        $assigns = $this->createAssignsFromParams($params);

        $methodBuilder = new MethodBuilder(MethodName::CONSTRUCT);
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

        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        $params = [];
        if ($constructClassMethod !== null) {
            foreach ($constructClassMethod->params as $param) {
                $params[] = $param;
                $new->args[] = new Arg($param->var);
            }
        }

        foreach ($properties as $property) {
            $propertyName = $this->nodeNameResolver->getName($property);
            $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);
            $new->args[] = new Arg($propertyFetch);
        }

        $return = new Return_($new);

        $methodBuilder = new MethodBuilder('create');
        $methodBuilder->setReturnType(new FullyQualified($className));
        $methodBuilder->makePublic();
        $methodBuilder->addStmt($return);
        $methodBuilder->addParams($params);

        return $methodBuilder->getNode();
    }

    private function createPropertyFromObjectType(ObjectType $objectType): Property
    {
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $property = $this->nodeFactory->createPrivateProperty($propertyName);

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $objectType);

        return $property;
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
}
