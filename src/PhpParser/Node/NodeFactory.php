<?php

declare (strict_types=1);
namespace Rector\PhpParser\Node;

use PhpParser\Builder\Method;
use PhpParser\Builder\Param as ParamBuilder;
use PhpParser\Builder\Property as PropertyBuilder;
use PhpParser\BuilderFactory;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
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
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Enum\ObjectReference;
use Rector\Exception\NotImplementedYetException;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeDecorator\PropertyTypeDecorator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\StaticTypeMapper\StaticTypeMapper;
/**
 * @see \Rector\Tests\PhpParser\Node\NodeFactoryTest
 */
final class NodeFactory
{
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
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeDecorator\PropertyTypeDecorator
     */
    private $propertyTypeDecorator;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var string
     */
    private const THIS = 'this';
    public function __construct(BuilderFactory $builderFactory, PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper, PropertyTypeDecorator $propertyTypeDecorator, SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->builderFactory = $builderFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->propertyTypeDecorator = $propertyTypeDecorator;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    /**
     * @param string|ObjectReference::* $className
     * Creates "\SomeClass::CONSTANT"
     */
    public function createClassConstFetch(string $className, string $constantName) : ClassConstFetch
    {
        $name = $this->createName($className);
        return $this->createClassConstFetchFromName($name, $constantName);
    }
    /**
     * @param string|ObjectReference::* $className
     * Creates "\SomeClass::class"
     */
    public function createClassConstReference(string $className) : ClassConstFetch
    {
        return $this->createClassConstFetch($className, 'class');
    }
    /**
     * Creates "['item', $variable]"
     *
     * @param mixed[] $items
     */
    public function createArray(array $items) : Array_
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
    public function createArgs(array $values) : array
    {
        return $this->builderFactory->args($values);
    }
    /**
     * Creates $this->property = $property;
     */
    public function createPropertyAssignment(string $propertyName) : Assign
    {
        $variable = new Variable($propertyName);
        return $this->createPropertyAssignmentWithExpr($propertyName, $variable);
    }
    /**
     * @api
     */
    public function createPropertyAssignmentWithExpr(string $propertyName, Expr $expr) : Assign
    {
        $propertyFetch = $this->createPropertyFetch(self::THIS, $propertyName);
        return new Assign($propertyFetch, $expr);
    }
    /**
     * @param mixed $argument
     */
    public function createArg($argument) : Arg
    {
        return new Arg(BuilderHelpers::normalizeValue($argument));
    }
    public function createPublicMethod(string $name) : ClassMethod
    {
        $method = new Method($name);
        $method->makePublic();
        return $method->getNode();
    }
    public function createParamFromNameAndType(string $name, ?Type $type) : Param
    {
        $param = new ParamBuilder($name);
        if ($type instanceof Type) {
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PARAM);
            if ($typeNode instanceof Node) {
                $param->setType($typeNode);
            }
        }
        return $param->getNode();
    }
    public function createPrivatePropertyFromNameAndType(string $name, ?Type $type) : Property
    {
        $propertyBuilder = new PropertyBuilder($name);
        $propertyBuilder->makePrivate();
        $property = $propertyBuilder->getNode();
        $this->propertyTypeDecorator->decorate($property, $type);
        return $property;
    }
    /**
     * @api symfony
     * @param mixed[] $arguments
     */
    public function createLocalMethodCall(string $method, array $arguments = []) : MethodCall
    {
        $variable = new Variable('this');
        return $this->createMethodCall($variable, $method, $arguments);
    }
    /**
     * @param mixed[] $arguments
     * @param \PhpParser\Node\Expr|string $exprOrVariableName
     */
    public function createMethodCall($exprOrVariableName, string $method, array $arguments = []) : MethodCall
    {
        $callerExpr = $this->createMethodCaller($exprOrVariableName);
        return $this->builderFactory->methodCall($callerExpr, $method, $arguments);
    }
    /**
     * @param string|\PhpParser\Node\Expr $variableNameOrExpr
     */
    public function createPropertyFetch($variableNameOrExpr, string $property) : PropertyFetch
    {
        $fetcherExpr = \is_string($variableNameOrExpr) ? new Variable($variableNameOrExpr) : $variableNameOrExpr;
        return $this->builderFactory->propertyFetch($fetcherExpr, $property);
    }
    /**
     * @api doctrine
     */
    public function createPrivateProperty(string $name) : Property
    {
        $propertyBuilder = new PropertyBuilder($name);
        $propertyBuilder->makePrivate();
        $property = $propertyBuilder->getNode();
        $this->phpDocInfoFactory->createFromNode($property);
        return $property;
    }
    /**
     * @param Expr[] $exprs
     */
    public function createConcat(array $exprs) : ?Concat
    {
        if (\count($exprs) < 2) {
            return null;
        }
        $previousConcat = \array_shift($exprs);
        foreach ($exprs as $expr) {
            $previousConcat = new Concat($previousConcat, $expr);
        }
        if (!$previousConcat instanceof Concat) {
            throw new ShouldNotHappenException();
        }
        return $previousConcat;
    }
    /**
     * @param string|ObjectReference::* $class
     * @param Node[] $args
     */
    public function createStaticCall(string $class, string $method, array $args = []) : StaticCall
    {
        $name = $this->createName($class);
        $args = $this->createArgs($args);
        return new StaticCall($name, $method, $args);
    }
    /**
     * @param mixed[] $arguments
     */
    public function createFuncCall(string $name, array $arguments = []) : FuncCall
    {
        $arguments = $this->createArgs($arguments);
        return new FuncCall(new Name($name), $arguments);
    }
    public function createSelfFetchConstant(string $constantName) : ClassConstFetch
    {
        $name = new Name(ObjectReference::SELF);
        return new ClassConstFetch($name, $constantName);
    }
    public function createNull() : ConstFetch
    {
        return new ConstFetch(new Name('null'));
    }
    public function createPromotedPropertyParam(PropertyMetadata $propertyMetadata) : Param
    {
        $paramBuilder = new ParamBuilder($propertyMetadata->getName());
        $propertyType = $propertyMetadata->getType();
        if ($propertyType instanceof Type) {
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
            if ($typeNode instanceof Node) {
                $paramBuilder->setType($typeNode);
            }
        }
        $param = $paramBuilder->getNode();
        $propertyFlags = $propertyMetadata->getFlags();
        $param->flags = $propertyFlags !== 0 ? $propertyFlags : Class_::MODIFIER_PRIVATE;
        return $param;
    }
    public function createFalse() : ConstFetch
    {
        return new ConstFetch(new Name('false'));
    }
    public function createTrue() : ConstFetch
    {
        return new ConstFetch(new Name('true'));
    }
    /**
     * @api phpunit
     * @param string|ObjectReference::* $constantName
     */
    public function createClassConstFetchFromName(Name $className, string $constantName) : ClassConstFetch
    {
        return $this->builderFactory->classConstFetch($className, $constantName);
    }
    /**
     * @param array<NotIdentical|BooleanAnd|BooleanOr|Identical> $newNodes
     */
    public function createReturnBooleanAnd(array $newNodes) : ?Expr
    {
        if ($newNodes === []) {
            return null;
        }
        if (\count($newNodes) === 1) {
            return $newNodes[0];
        }
        return $this->createBooleanAndFromNodes($newNodes);
    }
    /**
     * Setting all child nodes to null is needed to avoid reprint of invalid tokens
     * @see https://github.com/rectorphp/rector/issues/8712
     *
     * @template TNode as Node
     *
     * @param TNode $node
     * @return TNode
     */
    public function createReprintedNode(Node $node) : Node
    {
        // reset original node, to allow the printer to re-use the node
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($node, static function (Node $subNode) : Node {
            $subNode->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            return $subNode;
        });
        return $node;
    }
    /**
     * @param string|int|null $key
     * @param mixed $item
     */
    private function createArrayItem($item, $key = null) : ArrayItem
    {
        $arrayItem = null;
        if ($item instanceof Variable || $item instanceof MethodCall || $item instanceof StaticCall || $item instanceof FuncCall || $item instanceof Concat || $item instanceof Scalar || $item instanceof Cast || $item instanceof ConstFetch) {
            $arrayItem = new ArrayItem($item);
        } elseif ($item instanceof Identifier) {
            $string = new String_($item->toString());
            $arrayItem = new ArrayItem($string);
        } elseif (\is_scalar($item) || $item instanceof Array_) {
            $itemValue = BuilderHelpers::normalizeValue($item);
            $arrayItem = new ArrayItem($itemValue);
        } elseif (\is_array($item)) {
            $arrayItem = new ArrayItem($this->createArray($item));
        } elseif ($item === null || $item instanceof ClassConstFetch) {
            $itemValue = BuilderHelpers::normalizeValue($item);
            $arrayItem = new ArrayItem($itemValue);
        } elseif ($item instanceof Arg) {
            $arrayItem = new ArrayItem($item->value);
        }
        if ($arrayItem instanceof ArrayItem) {
            $this->decorateArrayItemWithKey($key, $arrayItem);
            return $arrayItem;
        }
        $nodeClass = \is_object($item) ? \get_class($item) : $item;
        throw new NotImplementedYetException(\sprintf('Not implemented yet. Go to "%s()" and add check for "%s" node.', __METHOD__, (string) $nodeClass));
    }
    /**
     * @param int|string|null $key
     */
    private function decorateArrayItemWithKey($key, ArrayItem $arrayItem) : void
    {
        if ($key === null) {
            return;
        }
        $arrayItem->key = BuilderHelpers::normalizeValue($key);
    }
    /**
     * @param Expr\BinaryOp[] $binaryOps
     */
    private function createBooleanAndFromNodes(array $binaryOps) : BooleanAnd
    {
        /** @var NotIdentical|BooleanAnd $mainBooleanAnd */
        $mainBooleanAnd = \array_shift($binaryOps);
        foreach ($binaryOps as $binaryOp) {
            $mainBooleanAnd = new BooleanAnd($mainBooleanAnd, $binaryOp);
        }
        /** @var BooleanAnd $mainBooleanAnd */
        return $mainBooleanAnd;
    }
    /**
     * @param string|ObjectReference::* $className
     * @return \PhpParser\Node\Name|\PhpParser\Node\Name\FullyQualified
     */
    private function createName(string $className)
    {
        if (\in_array($className, [ObjectReference::PARENT, ObjectReference::SELF, ObjectReference::STATIC], \true)) {
            return new Name($className);
        }
        return new FullyQualified($className);
    }
    /**
     * @param \PhpParser\Node\Expr|string $exprOrVariableName
     * @return \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\Variable|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticPropertyFetch|\PhpParser\Node\Expr
     */
    private function createMethodCaller($exprOrVariableName)
    {
        if (\is_string($exprOrVariableName)) {
            return new Variable($exprOrVariableName);
        }
        if ($exprOrVariableName instanceof PropertyFetch) {
            return new PropertyFetch($exprOrVariableName->var, $exprOrVariableName->name);
        }
        if ($exprOrVariableName instanceof StaticPropertyFetch) {
            return new StaticPropertyFetch($exprOrVariableName->class, $exprOrVariableName->name);
        }
        if ($exprOrVariableName instanceof MethodCall) {
            return new MethodCall($exprOrVariableName->var, $exprOrVariableName->name, $exprOrVariableName->args);
        }
        return $exprOrVariableName;
    }
}
