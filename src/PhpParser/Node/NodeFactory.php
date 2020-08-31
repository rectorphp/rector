<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node;

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
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Error;
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
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Exception\NotImplementedException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Builder\MethodBuilder;
use Rector\Core\PhpParser\Builder\ParamBuilder;
use Rector\Core\PhpParser\Builder\PropertyBuilder;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\StaticTypeMapper;

/**
 * @see \Rector\Core\Tests\PhpParser\Node\NodeFactoryTest
 */
final class NodeFactory
{
    /**
     * @var string
     */
    private const THIS = 'this';

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

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        BuilderFactory $builderFactory,
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpVersionProvider $phpVersionProvider,
        StaticTypeMapper $staticTypeMapper,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->builderFactory = $builderFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
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
     * @param mixed[] $values
     * @return Arg[]
     */
    public function createArgs(array $values): array
    {
        $normalizedValues = [];
        foreach ($values as $key => $value) {
            $normalizedValues[$key] = $this->normalizeArgValue($value);
        }

        return $this->builderFactory->args($normalizedValues);
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
        $propertyFetch = $this->createPropertyFetch(self::THIS, $propertyName);

        return new Assign($propertyFetch, $expr);
    }

    /**
     * @param mixed $argument
     */
    public function createArg($argument): Arg
    {
        return new Arg(BuilderHelpers::normalizeValue($argument));
    }

    public function createPublicMethod(string $name): ClassMethod
    {
        $methodBuilder = new MethodBuilder($name);
        $methodBuilder->makePublic();

        return $methodBuilder->getNode();
    }

    public function createParamFromNameAndType(string $name, ?Type $type): Param
    {
        $paramBuilder = new ParamBuilder($name);

        if ($type !== null) {
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type);
            if ($typeNode !== null) {
                $paramBuilder->setType($typeNode);
            }
        }

        return $paramBuilder->getNode();
    }

    public function createPublicInjectPropertyFromNameAndType(string $name, ?Type $type): Property
    {
        $propertyBuilder = new PropertyBuilder($name);
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
        $propertyBuilder = new PropertyBuilder($name);
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
            new Identifier(MethodName::CONSTRUCT),
            $this->convertParamNodesToArgNodes($params)
        );
    }

    public function createStaticProtectedPropertyWithDefault(string $name, Node $node): Property
    {
        $propertyBuilder = new PropertyBuilder($name);
        $propertyBuilder->makeProtected();
        $propertyBuilder->makeStatic();
        $propertyBuilder->setDefault($node);

        $property = $propertyBuilder->getNode();

        $this->decorateParentPropertyProperty($property);

        return $property;
    }

    public function createPrivateProperty(string $name): Property
    {
        $propertyBuilder = new PropertyBuilder($name);
        $propertyBuilder->makePrivate();

        $property = $propertyBuilder->getNode();
        $this->decorateParentPropertyProperty($property);

        $this->phpDocInfoFactory->createFromNode($property);

        return $property;
    }

    public function createPublicProperty(string $name): Property
    {
        $propertyBuilder = new PropertyBuilder($name);
        $propertyBuilder->makePublic();

        $property = $propertyBuilder->getNode();
        $this->decorateParentPropertyProperty($property);

        $this->phpDocInfoFactory->createFromNode($property);

        return $property;
    }

    /**
     * @param mixed $value
     */
    public function createPrivateClassConst(string $name, $value): ClassConst
    {
        return $this->createClassConstant($name, $value, Class_::MODIFIER_PRIVATE);
    }

    /**
     * @param mixed $value
     */
    public function createPublicClassConst(string $name, $value): ClassConst
    {
        return $this->createClassConstant($name, $value, Class_::MODIFIER_PUBLIC);
    }

    /**
     * @param Identifier|Name|NullableType|UnionType|null $typeNode
     */
    public function createGetterClassMethodFromNameAndType(string $propertyName, ?Node $typeNode): ClassMethod
    {
        $getterMethod = 'get' . ucfirst($propertyName);

        $methodBuilder = new MethodBuilder($getterMethod);
        $methodBuilder->makePublic();

        $propertyFetch = new PropertyFetch(new Variable(self::THIS), $propertyName);

        $return = new Return_($propertyFetch);
        $methodBuilder->addStmt($return);

        if ($typeNode !== null) {
            $methodBuilder->setReturnType($typeNode);
        }

        return $methodBuilder->getNode();
    }

    /**
     * @param Expr[] $exprs
     */
    public function createConcat(array $exprs): ?\PhpParser\Node\Expr
    {
        if ($exprs === []) {
            return null;
        }

        /** @var Expr $previousConcat */
        $previousConcat = array_shift($exprs);

        foreach ($exprs as $expr) {
            $previousConcat = new Concat($previousConcat, $expr);
        }

        return $previousConcat;
    }

    public function createClosureFromClassMethod(ClassMethod $classMethod): Closure
    {
        $classMethodName = $this->nodeNameResolver->getName($classMethod);
        $args = $this->createArgs($classMethod->params);

        $methodCall = new MethodCall(new Variable(self::THIS), $classMethodName, $args);
        $return = new Return_($methodCall);

        return new Closure([
            'params' => $classMethod->params,
            'stmts' => [$return],
            'returnType' => $classMethod->returnType,
        ]);
    }

    /**
     * @param mixed $item
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
        } elseif (is_scalar($item) || $item instanceof Array_) {
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

    /**
     * @param mixed $value
     * @return mixed|Error|\PhpParser\Node\Expr\Variable
     */
    private function normalizeArgValue($value)
    {
        if ($value instanceof Param) {
            return $value->var;
        }

        return $value;
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

                if ($type instanceof GenericObjectType) {
                    $phpDocInfo->changeVarType($type);
                }

                return;
            }
        }

        $phpDocInfo->changeVarType($type);
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

    /**
     * @param mixed $value
     */
    private function createClassConstant(string $name, $value, int $modifier): ClassConst
    {
        $value = BuilderHelpers::normalizeValue($value);

        $const = new Const_($name, $value);
        $classConst = new ClassConst([$const]);
        $classConst->flags |= $modifier;

        // add @var type by default
        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($value);

        if (! $staticType instanceof MixedType) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($classConst);
            $phpDocInfo->changeVarType($staticType);
        }

        return $classConst;
    }
}
