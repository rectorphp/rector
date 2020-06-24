<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node;

use PhpParser\Builder\Method;
use PhpParser\BuilderFactory;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Exception\NotImplementedException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\StaticTypeMapper;

/**
 * @see \Rector\Core\Tests\PhpParser\Node\NodeFactoryTest
 */
final class NodeFactory
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    public function __construct(
        BuilderFactory $builderFactory,
        StaticTypeMapper $staticTypeMapper,
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpVersionProvider $phpVersionProvider
    ) {
        $this->builderFactory = $builderFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpVersionProvider = $phpVersionProvider;
    }

    /**
     * Creates "\SomeClass::CONSTANT"
     */
    public function createClassConstFetch(string $className, string $constantName): ClassConstFetch
    {
        $classNameNode = in_array($className, ['static', 'parent', 'self'], true) ? new Name(
            $className
        ) : new FullyQualified($className);

        $classConstFetchNode = $this->builderFactory->classConstFetch($classNameNode, $constantName);
        $classConstFetchNode->class->setAttribute(AttributeKey::RESOLVED_NAME, $classNameNode);

        return $classConstFetchNode;
    }

    /**
     * Creates "\SomeClass::class"
     */
    public function createClassConstReference(string $className): ClassConstFetch
    {
        return $this->createClassConstFetch($className, 'class');
    }

    /**
     * Creates "['item', $variable]"
     *
     * @param mixed[] $items
     */
    public function createArray(array $items): Array_
    {
        $arrayItems = [];

        $defaultKey = 0;
        foreach ($items as $key => $item) {
            $customKey = $key !== $defaultKey ? $key : null;
            $arrayItems[] = $this->createArrayItem($item, $customKey);

            ++$defaultKey;
        }

        return new Array_($arrayItems);
    }

    /**
     * Creates "($args)"
     *
     * @param mixed[] $arguments
     * @return Arg[]
     */
    public function createArgs(array $arguments): array
    {
        return $this->builderFactory->args($arguments);
    }

    /**
     * Creates $this->property = $property;
     */
    public function createPropertyAssignment(string $propertyName): Assign
    {
        $variable = new Variable($propertyName);

        return $this->createPropertyAssignmentWithExpr($propertyName, $variable);
    }

    public function createPropertyAssignmentWithExpr(string $propertyName, Expr $expr): Assign
    {
        $leftExprNode = $this->createPropertyFetch('this', $propertyName);

        return new Assign($leftExprNode, $expr);
    }

    /**
     * Creates "($arg)"
     */
    public function createArg($argument): Arg
    {
        return new Arg(BuilderHelpers::normalizeValue($argument));
    }

    public function createPublicMethod(string $name): ClassMethod
    {
        $methodBuilder = $this->builderFactory->method($name);
        $methodBuilder->makePublic();

        return $methodBuilder->getNode();
    }

    public function createParamFromNameAndType(string $name, ?Type $type): Param
    {
        $paramBuild = $this->builderFactory->param($name);

        if ($type !== null) {
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type);
            if ($typeNode !== null) {
                $paramBuild->setType($typeNode);
            }
        }

        return $paramBuild->getNode();
    }

    public function createPublicInjectPropertyFromNameAndType(string $name, ?Type $type): Property
    {
        $propertyBuilder = $this->builderFactory->property($name);
        $propertyBuilder->makePublic();

        $property = $propertyBuilder->getNode();

        $this->addPropertyType($property, $type);
        $this->decorateParentPropertyProperty($property);

        // add @inject
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        }

        $phpDocInfo->addBareTag('inject');

        return $property;
    }

    public function createPrivatePropertyFromNameAndType(string $name, ?Type $type): Property
    {
        $propertyBuilder = $this->builderFactory->property($name);
        $propertyBuilder->makePrivate();

        $property = $propertyBuilder->getNode();

        $this->addPropertyType($property, $type);

        $this->decorateParentPropertyProperty($property);

        return $property;
    }

    /**
     * @param string|Expr $variable
     * @param mixed[] $arguments
     */
    public function createMethodCall($variable, string $method, array $arguments = []): MethodCall
    {
        if (is_string($variable)) {
            $variable = new Variable($variable);
        }

        if ($variable instanceof PropertyFetch) {
            $variable = new PropertyFetch($variable->var, $variable->name);
        }

        if ($variable instanceof StaticPropertyFetch) {
            $variable = new StaticPropertyFetch($variable->class, $variable->name);
        }

        if ($variable instanceof MethodCall) {
            $variable = new MethodCall($variable->var, $variable->name, $variable->args);
        }

        $methodCallNode = $this->builderFactory->methodCall($variable, $method, $arguments);

        $variable->setAttribute(AttributeKey::PARENT_NODE, $methodCallNode);

        return $methodCallNode;
    }

    /**
     * @param string|Expr $variable
     */
    public function createPropertyFetch($variable, string $property): PropertyFetch
    {
        if (is_string($variable)) {
            $variable = new Variable($variable);
        }

        return $this->builderFactory->propertyFetch($variable, $property);
    }

    /**
     * @param Param[] $params
     */
    public function createParentConstructWithParams(array $params): StaticCall
    {
        return new StaticCall(
            new Name('parent'),
            new Identifier('__construct'),
            $this->convertParamNodesToArgNodes($params)
        );
    }

    public function createStaticProtectedPropertyWithDefault(string $name, Node $node): Property
    {
        $propertyBuilder = $this->builderFactory->property($name);
        $propertyBuilder->makeProtected();
        $propertyBuilder->makeStatic();
        $propertyBuilder->setDefault($node);

        $property = $propertyBuilder->getNode();

        $this->decorateParentPropertyProperty($property);

        return $property;
    }

    public function createPrivateProperty(string $name): Property
    {
        $propertyBuilder = $this->builderFactory->property($name);
        $propertyBuilder->makePrivate();

        $property = $propertyBuilder->getNode();
        $this->decorateParentPropertyProperty($property);

        $this->phpDocInfoFactory->createFromNode($property);

        return $property;
    }

    public function createPublicProperty(string $name): Property
    {
        $propertyBuilder = $this->builderFactory->property($name);
        $propertyBuilder->makePublic();

        $property = $propertyBuilder->getNode();
        $this->decorateParentPropertyProperty($property);

        $this->phpDocInfoFactory->createFromNode($property);

        return $property;
    }

    public function createPrivateClassConst(string $name, $value): ClassConst
    {
        $const = new Const_($name, $value);
        $classConst = new ClassConst([$const]);
        $classConst->flags |= Class_::MODIFIER_PRIVATE;

        // add @var type by default
        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($value);
        if (! $staticType instanceof MixedType) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($classConst);
            $phpDocInfo->changeVarType($staticType);
        }

        return $classConst;
    }

    /**
     * @param Identifier|Name|NullableType|UnionType|null $typeNode
     */
    public function createGetterClassMethodFromNameAndType(string $propertyName, ?Node $typeNode): ClassMethod
    {
        $getterMethod = 'get' . ucfirst($propertyName);

        $methodBuilder = new Method($getterMethod);
        $methodBuilder->makePublic();

        $localPropertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

        $return = new Return_($localPropertyFetch);
        $methodBuilder->addStmt($return);

        if ($typeNode !== null) {
            $methodBuilder->setReturnType($typeNode);
        }

        return $methodBuilder->getNode();
    }

    /**
     * @param string|int|null $key
     */
    private function createArrayItem($item, $key = null): ArrayItem
    {
        $arrayItem = null;

        if ($item instanceof Variable
            || $item instanceof MethodCall
            || $item instanceof StaticCall
            || $item instanceof FuncCall
            || $item instanceof Concat
            || $item instanceof Scalar
        ) {
            $arrayItem = new ArrayItem($item);
        } elseif ($item instanceof Identifier) {
            $string = new String_($item->toString());
            $arrayItem = new ArrayItem($string);
        } elseif (is_scalar($item)) {
            $itemValue = BuilderHelpers::normalizeValue($item);
            $arrayItem = new ArrayItem($itemValue);
        } elseif (is_array($item)) {
            $arrayItem = new ArrayItem($this->createArray($item));
        }

        if ($arrayItem !== null) {
            if ($key === null) {
                return $arrayItem;
            }

            $arrayItem->key = BuilderHelpers::normalizeValue($key);

            return $arrayItem;
        }

        if ($item instanceof ClassConstFetch) {
            $itemValue = BuilderHelpers::normalizeValue($item);
            return new ArrayItem($itemValue);
        }

        throw new NotImplementedException(sprintf(
            'Not implemented yet. Go to "%s()" and add check for "%s" node.',
            __METHOD__,
            is_object($item) ? get_class($item) : $item
        ));
    }

    private function decorateParentPropertyProperty(Property $property): void
    {
        // complete property property parent, needed for other operations
        $propertyProperty = $property->props[0];
        $propertyProperty->setAttribute(AttributeKey::PARENT_NODE, $property);
    }

    /**
     * @param Param[] $paramNodes
     * @return Arg[]
     */
    private function convertParamNodesToArgNodes(array $paramNodes): array
    {
        $args = [];
        foreach ($paramNodes as $paramNode) {
            $args[] = new Arg($paramNode->var);
        }

        return $args;
    }

    private function addPropertyType(Property $property, ?Type $type): void
    {
        if ($type === null) {
            return;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        if ($this->phpVersionProvider->isAtLeast(PhpVersionFeature::TYPED_PROPERTIES)) {
            $phpParserType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type);

            if ($phpParserType !== null) {
                $property->type = $phpParserType;
                return;
            }
        }

        $phpDocInfo->changeVarType($type);
    }
}
