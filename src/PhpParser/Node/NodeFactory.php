<?php

declare (strict_types=1);
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
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
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
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeDecorator\PropertyTypeDecorator;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;
use RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder;
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
     * @readonly
     * @var \PhpParser\BuilderFactory
     */
    private $builderFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
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
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\CurrentNodeProvider
     */
    private $currentNodeProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $reflectionAstResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeDecorator\PropertyTypeDecorator
     */
    private $propertyTypeDecorator;
    public function __construct(\PhpParser\BuilderFactory $builderFactory, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\Core\Configuration\CurrentNodeProvider $currentNodeProvider, \Rector\Core\PhpParser\AstResolver $reflectionAstResolver, \Rector\Core\NodeDecorator\PropertyTypeDecorator $propertyTypeDecorator)
    {
        $this->builderFactory = $builderFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->reflectionAstResolver = $reflectionAstResolver;
        $this->propertyTypeDecorator = $propertyTypeDecorator;
    }
    /**
     * Creates "SomeClass::CONSTANT"
     */
    public function createShortClassConstFetch(string $shortClassName, string $constantName) : \PhpParser\Node\Expr\ClassConstFetch
    {
        $name = new \PhpParser\Node\Name($shortClassName);
        return $this->createClassConstFetchFromName($name, $constantName);
    }
    /**
     * Creates "\SomeClass::CONSTANT"
     * @param string|\Rector\Core\Enum\ObjectReference $className
     */
    public function createClassConstFetch($className, string $constantName) : \PhpParser\Node\Expr\ClassConstFetch
    {
        $name = $this->createName($className);
        return $this->createClassConstFetchFromName($name, $constantName);
    }
    /**
     * Creates "\SomeClass::class"
     * @param string|\Rector\Core\Enum\ObjectReference $className
     */
    public function createClassConstReference($className) : \PhpParser\Node\Expr\ClassConstFetch
    {
        return $this->createClassConstFetch($className, 'class');
    }
    /**
     * Creates "['item', $variable]"
     *
     * @param mixed[] $items
     */
    public function createArray(array $items) : \PhpParser\Node\Expr\Array_
    {
        $arrayItems = [];
        $defaultKey = 0;
        foreach ($items as $key => $item) {
            $customKey = $key !== $defaultKey ? $key : null;
            $arrayItems[] = $this->createArrayItem($item, $customKey);
            ++$defaultKey;
        }
        return new \PhpParser\Node\Expr\Array_($arrayItems);
    }
    /**
     * Creates "($args)"
     *
     * @param mixed[] $values
     * @return Arg[]
     */
    public function createArgs(array $values) : array
    {
        \array_walk($values, function ($value) {
            return $this->normalizeArgValue($value);
        });
        return $this->builderFactory->args($values);
    }
    /**
     * Creates $this->property = $property;
     */
    public function createPropertyAssignment(string $propertyName) : \PhpParser\Node\Expr\Assign
    {
        $variable = new \PhpParser\Node\Expr\Variable($propertyName);
        return $this->createPropertyAssignmentWithExpr($propertyName, $variable);
    }
    public function createPropertyAssignmentWithExpr(string $propertyName, \PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr\Assign
    {
        $propertyFetch = $this->createPropertyFetch(self::THIS, $propertyName);
        return new \PhpParser\Node\Expr\Assign($propertyFetch, $expr);
    }
    /**
     * @param mixed $argument
     */
    public function createArg($argument) : \PhpParser\Node\Arg
    {
        return new \PhpParser\Node\Arg(\PhpParser\BuilderHelpers::normalizeValue($argument));
    }
    public function createPublicMethod(string $name) : \PhpParser\Node\Stmt\ClassMethod
    {
        $methodBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder($name);
        $methodBuilder->makePublic();
        return $methodBuilder->getNode();
    }
    public function createParamFromNameAndType(string $name, ?\PHPStan\Type\Type $type) : \PhpParser\Node\Param
    {
        $paramBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder($name);
        if ($type !== null) {
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PARAM());
            if ($typeNode !== null) {
                $paramBuilder->setType($typeNode);
            }
        }
        return $paramBuilder->getNode();
    }
    public function createPublicInjectPropertyFromNameAndType(string $name, ?\PHPStan\Type\Type $type) : \PhpParser\Node\Stmt\Property
    {
        $propertyBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder($name);
        $propertyBuilder->makePublic();
        $property = $propertyBuilder->getNode();
        $this->propertyTypeDecorator->decorate($property, $type);
        // add @inject
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $phpDocInfo->addPhpDocTagNode(new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode('@inject', new \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode('')));
        return $property;
    }
    public function createPrivatePropertyFromNameAndType(string $name, ?\PHPStan\Type\Type $type) : \PhpParser\Node\Stmt\Property
    {
        $propertyBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder($name);
        $propertyBuilder->makePrivate();
        $property = $propertyBuilder->getNode();
        $this->propertyTypeDecorator->decorate($property, $type);
        return $property;
    }
    /**
     * @param mixed[] $arguments
     */
    public function createLocalMethodCall(string $method, array $arguments = []) : \PhpParser\Node\Expr\MethodCall
    {
        $variable = new \PhpParser\Node\Expr\Variable('this');
        return $this->createMethodCall($variable, $method, $arguments);
    }
    /**
     * @param mixed[] $arguments
     * @param string|\PhpParser\Node\Expr $variable
     */
    public function createMethodCall($variable, string $method, array $arguments = []) : \PhpParser\Node\Expr\MethodCall
    {
        if (\is_string($variable)) {
            $variable = new \PhpParser\Node\Expr\Variable($variable);
        }
        if ($variable instanceof \PhpParser\Node\Expr\PropertyFetch) {
            $variable = new \PhpParser\Node\Expr\PropertyFetch($variable->var, $variable->name);
        }
        if ($variable instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
            $variable = new \PhpParser\Node\Expr\StaticPropertyFetch($variable->class, $variable->name);
        }
        if ($variable instanceof \PhpParser\Node\Expr\MethodCall) {
            $variable = new \PhpParser\Node\Expr\MethodCall($variable->var, $variable->name, $variable->args);
        }
        return $this->builderFactory->methodCall($variable, $method, $arguments);
    }
    /**
     * @param string|\PhpParser\Node\Expr $variable
     */
    public function createPropertyFetch($variable, string $property) : \PhpParser\Node\Expr\PropertyFetch
    {
        if (\is_string($variable)) {
            $variable = new \PhpParser\Node\Expr\Variable($variable);
        }
        return $this->builderFactory->propertyFetch($variable, $property);
    }
    /**
     * @param Param[] $params
     */
    public function createParentConstructWithParams(array $params) : \PhpParser\Node\Expr\StaticCall
    {
        return new \PhpParser\Node\Expr\StaticCall(new \PhpParser\Node\Name(\Rector\Core\Enum\ObjectReference::PARENT()->getValue()), new \PhpParser\Node\Identifier(\Rector\Core\ValueObject\MethodName::CONSTRUCT), $this->createArgsFromParams($params));
    }
    public function createProperty(string $name) : \PhpParser\Node\Stmt\Property
    {
        $propertyBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder($name);
        $property = $propertyBuilder->getNode();
        $this->phpDocInfoFactory->createFromNode($property);
        return $property;
    }
    public function createPrivateProperty(string $name) : \PhpParser\Node\Stmt\Property
    {
        $propertyBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder($name);
        $propertyBuilder->makePrivate();
        $property = $propertyBuilder->getNode();
        $this->phpDocInfoFactory->createFromNode($property);
        return $property;
    }
    public function createPublicProperty(string $name) : \PhpParser\Node\Stmt\Property
    {
        $propertyBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder($name);
        $propertyBuilder->makePublic();
        $property = $propertyBuilder->getNode();
        $this->phpDocInfoFactory->createFromNode($property);
        return $property;
    }
    public function createGetterClassMethod(string $propertyName, \PHPStan\Type\Type $type) : \PhpParser\Node\Stmt\ClassMethod
    {
        $methodBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder('get' . \ucfirst($propertyName));
        $methodBuilder->makePublic();
        $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable(self::THIS), $propertyName);
        $return = new \PhpParser\Node\Stmt\Return_($propertyFetch);
        $methodBuilder->addStmt($return);
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::RETURN());
        if ($typeNode !== null) {
            $methodBuilder->setReturnType($typeNode);
        }
        return $methodBuilder->getNode();
    }
    public function createSetterClassMethod(string $propertyName, \PHPStan\Type\Type $type) : \PhpParser\Node\Stmt\ClassMethod
    {
        $methodBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder('set' . \ucfirst($propertyName));
        $methodBuilder->makePublic();
        $variable = new \PhpParser\Node\Expr\Variable($propertyName);
        $param = $this->createParamWithType($variable, $type);
        $methodBuilder->addParam($param);
        $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable(self::THIS), $propertyName);
        $assign = new \PhpParser\Node\Expr\Assign($propertyFetch, $variable);
        $methodBuilder->addStmt($assign);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::VOID_TYPE)) {
            $methodBuilder->setReturnType(new \PhpParser\Node\Name('void'));
        }
        return $methodBuilder->getNode();
    }
    /**
     * @param Expr[] $exprs
     */
    public function createConcat(array $exprs) : ?\PhpParser\Node\Expr\BinaryOp\Concat
    {
        if (\count($exprs) < 2) {
            return null;
        }
        $previousConcat = \array_shift($exprs);
        foreach ($exprs as $expr) {
            $previousConcat = new \PhpParser\Node\Expr\BinaryOp\Concat($previousConcat, $expr);
        }
        if (!$previousConcat instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $previousConcat;
    }
    public function createClosureFromClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : \PhpParser\Node\Expr\Closure
    {
        $classMethodName = $this->nodeNameResolver->getName($classMethod);
        $args = $this->createArgs($classMethod->params);
        $methodCall = new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable(self::THIS), $classMethodName, $args);
        $return = new \PhpParser\Node\Stmt\Return_($methodCall);
        return new \PhpParser\Node\Expr\Closure(['params' => $classMethod->params, 'stmts' => [$return], 'returnType' => $classMethod->returnType]);
    }
    /**
     * @param string[] $names
     * @return Use_[]
     */
    public function createUsesFromNames(array $names) : array
    {
        $uses = [];
        foreach ($names as $name) {
            $useUse = new \PhpParser\Node\Stmt\UseUse(new \PhpParser\Node\Name($name));
            $uses[] = new \PhpParser\Node\Stmt\Use_([$useUse]);
        }
        return $uses;
    }
    /**
     * @param Node[] $args
     * @param string|\Rector\Core\Enum\ObjectReference $class
     */
    public function createStaticCall($class, string $method, array $args = []) : \PhpParser\Node\Expr\StaticCall
    {
        $name = $this->createName($class);
        $args = $this->createArgs($args);
        return new \PhpParser\Node\Expr\StaticCall($name, $method, $args);
    }
    /**
     * @param mixed[] $arguments
     */
    public function createFuncCall(string $name, array $arguments = []) : \PhpParser\Node\Expr\FuncCall
    {
        $arguments = $this->createArgs($arguments);
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name($name), $arguments);
    }
    public function createSelfFetchConstant(string $constantName) : \PhpParser\Node\Expr\ClassConstFetch
    {
        $name = new \PhpParser\Node\Name(\Rector\Core\Enum\ObjectReference::SELF()->getValue());
        return new \PhpParser\Node\Expr\ClassConstFetch($name, $constantName);
    }
    /**
     * @param Param[] $params
     * @return Arg[]
     */
    public function createArgsFromParams(array $params) : array
    {
        $args = [];
        foreach ($params as $param) {
            $args[] = new \PhpParser\Node\Arg($param->var);
        }
        return $args;
    }
    public function createNull() : \PhpParser\Node\Expr\ConstFetch
    {
        return new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('null'));
    }
    public function createPromotedPropertyParam(\Rector\PostRector\ValueObject\PropertyMetadata $propertyMetadata) : \PhpParser\Node\Param
    {
        $paramBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder($propertyMetadata->getName());
        $propertyType = $propertyMetadata->getType();
        if ($propertyType !== null) {
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY());
            if ($typeNode !== null) {
                $paramBuilder->setType($typeNode);
            }
        }
        $param = $paramBuilder->getNode();
        $propertyFlags = $propertyMetadata->getFlags();
        $param->flags = $propertyFlags !== 0 ? $propertyFlags : \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE;
        return $param;
    }
    public function createFalse() : \PhpParser\Node\Expr\ConstFetch
    {
        return new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('false'));
    }
    public function createTrue() : \PhpParser\Node\Expr\ConstFetch
    {
        return new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('true'));
    }
    public function createClosureFromMethodReflection(\PHPStan\Reflection\MethodReflection $methodReflection) : \PhpParser\Node\Expr\Closure
    {
        $classMethod = $this->reflectionAstResolver->resolveClassMethodFromMethodReflection($methodReflection);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $this->createClosureFromClassMethod($classMethod);
    }
    public function createClassConstFetchFromName(\PhpParser\Node\Name $className, string $constantName) : \PhpParser\Node\Expr\ClassConstFetch
    {
        $classConstFetch = $this->builderFactory->classConstFetch($className, $constantName);
        $classNameString = $className->toString();
        if (\in_array($classNameString, [\Rector\Core\Enum\ObjectReference::SELF()->getValue(), \Rector\Core\Enum\ObjectReference::STATIC()->getValue()], \true)) {
            $currentNode = $this->currentNodeProvider->getNode();
            if ($currentNode !== null) {
                $classConstFetch->class->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::RESOLVED_NAME, $className);
            }
        } else {
            $classConstFetch->class->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::RESOLVED_NAME, $classNameString);
        }
        return $classConstFetch;
    }
    /**
     * @param array<NotIdentical|BooleanAnd> $newNodes
     */
    public function createReturnBooleanAnd(array $newNodes) : ?\PhpParser\Node\Expr
    {
        if ($newNodes === []) {
            return null;
        }
        if (\count($newNodes) === 1) {
            return $newNodes[0];
        }
        return $this->createBooleanAndFromNodes($newNodes);
    }
    public function createClassConstant(string $name, \PhpParser\Node\Expr $expr, int $modifier) : \PhpParser\Node\Stmt\ClassConst
    {
        $expr = \PhpParser\BuilderHelpers::normalizeValue($expr);
        $const = new \PhpParser\Node\Const_($name, $expr);
        $classConst = new \PhpParser\Node\Stmt\ClassConst([$const]);
        $classConst->flags |= $modifier;
        // add @var type by default
        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($expr);
        if (!$staticType instanceof \PHPStan\Type\MixedType) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classConst);
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $staticType);
        }
        return $classConst;
    }
    /**
     * @param mixed $item
     * @param string|int|null $key
     */
    private function createArrayItem($item, $key = null) : \PhpParser\Node\Expr\ArrayItem
    {
        $arrayItem = null;
        if ($item instanceof \PhpParser\Node\Expr\Variable || $item instanceof \PhpParser\Node\Expr\MethodCall || $item instanceof \PhpParser\Node\Expr\StaticCall || $item instanceof \PhpParser\Node\Expr\FuncCall || $item instanceof \PhpParser\Node\Expr\BinaryOp\Concat || $item instanceof \PhpParser\Node\Scalar || $item instanceof \PhpParser\Node\Expr\Cast) {
            $arrayItem = new \PhpParser\Node\Expr\ArrayItem($item);
        } elseif ($item instanceof \PhpParser\Node\Identifier) {
            $string = new \PhpParser\Node\Scalar\String_($item->toString());
            $arrayItem = new \PhpParser\Node\Expr\ArrayItem($string);
        } elseif (\is_scalar($item) || $item instanceof \PhpParser\Node\Expr\Array_) {
            $itemValue = \PhpParser\BuilderHelpers::normalizeValue($item);
            $arrayItem = new \PhpParser\Node\Expr\ArrayItem($itemValue);
        } elseif (\is_array($item)) {
            $arrayItem = new \PhpParser\Node\Expr\ArrayItem($this->createArray($item));
        }
        if ($item === null || $item instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            $itemValue = \PhpParser\BuilderHelpers::normalizeValue($item);
            $arrayItem = new \PhpParser\Node\Expr\ArrayItem($itemValue);
        }
        if ($item instanceof \PhpParser\Node\Arg) {
            $arrayItem = new \PhpParser\Node\Expr\ArrayItem($item->value);
        }
        if ($arrayItem !== null) {
            $this->decorateArrayItemWithKey($key, $arrayItem);
            return $arrayItem;
        }
        $nodeClass = \is_object($item) ? \get_class($item) : $item;
        throw new \Rector\Core\Exception\NotImplementedYetException(\sprintf('Not implemented yet. Go to "%s()" and add check for "%s" node.', __METHOD__, (string) $nodeClass));
    }
    /**
     * @param mixed $value
     * @return mixed|Error|Variable
     */
    private function normalizeArgValue($value)
    {
        if ($value instanceof \PhpParser\Node\Param) {
            return $value->var;
        }
        return $value;
    }
    /**
     * @param int|string|null $key
     */
    private function decorateArrayItemWithKey($key, \PhpParser\Node\Expr\ArrayItem $arrayItem) : void
    {
        if ($key !== null) {
            $arrayItem->key = \PhpParser\BuilderHelpers::normalizeValue($key);
        }
    }
    /**
     * @param NotIdentical[]|BooleanAnd[] $exprs
     */
    private function createBooleanAndFromNodes(array $exprs) : \PhpParser\Node\Expr\BinaryOp\BooleanAnd
    {
        /** @var NotIdentical|BooleanAnd $booleanAnd */
        $booleanAnd = \array_shift($exprs);
        foreach ($exprs as $expr) {
            $booleanAnd = new \PhpParser\Node\Expr\BinaryOp\BooleanAnd($booleanAnd, $expr);
        }
        /** @var BooleanAnd $booleanAnd */
        return $booleanAnd;
    }
    private function createParamWithType(\PhpParser\Node\Expr\Variable $variable, \PHPStan\Type\Type $type) : \PhpParser\Node\Param
    {
        $param = new \PhpParser\Node\Param($variable);
        $phpParserTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PARAM());
        $param->type = $phpParserTypeNode;
        return $param;
    }
    /**
     * @param string|\Rector\Core\Enum\ObjectReference $className
     * @return \PhpParser\Node\Name\FullyQualified|\PhpParser\Node\Name
     */
    private function createName($className)
    {
        if ($className instanceof \Rector\Core\Enum\ObjectReference) {
            return new \PhpParser\Node\Name($className->getValue());
        }
        if (\Rector\Core\Enum\ObjectReference::isValid($className)) {
            return new \PhpParser\Node\Name($className);
        }
        return new \PhpParser\Node\Name\FullyQualified($className);
    }
}
