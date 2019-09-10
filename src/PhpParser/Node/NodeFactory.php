<?php declare(strict_types=1);

namespace Rector\PhpParser\Node;

use PhpParser\BuilderFactory;
use PhpParser\BuilderHelpers;
use PhpParser\Comment\Doc;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\Exception\NotImplementedException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\StaticTypeMapper;

/**
 * @see \Rector\Tests\PhpParser\Node\NodeFactoryTest
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

    public function __construct(BuilderFactory $builderFactory, StaticTypeMapper $staticTypeMapper)
    {
        $this->builderFactory = $builderFactory;
        $this->staticTypeMapper = $staticTypeMapper;
    }

    /**
     * Creates "\SomeClass::CONSTANT"
     */
    public function createClassConstant(string $className, string $constantName): ClassConstFetch
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
    public function createClassConstantReference(string $className): ClassConstFetch
    {
        $nameNode = new FullyQualified($className);

        return $this->builderFactory->classConstFetch($nameNode, 'class');
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
     *
     * @param mixed $argument
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

    public function createParamFromVariableInfo(VariableInfo $variableInfo): Param
    {
        $paramBuild = $this->builderFactory->param($variableInfo->getName());

        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($variableInfo->getType());

        if ($typeNode) {
            $paramBuild->setType($typeNode);
        }

        return $paramBuild->getNode();
    }

    public function createPrivatePropertyFromVariableInfo(VariableInfo $variableInfo): Property
    {
        $docComment = $this->createVarDoc($variableInfo->getType());

        $propertyBuilder = $this->builderFactory->property($variableInfo->getName());
        $propertyBuilder->makePrivate();
        $propertyBuilder->setDocComment($docComment);

        return $propertyBuilder->getNode();
    }

    /**
     * @param string|Expr $variable
     * @param mixed[] $arguments
     */
    public function createMethodCall($variable, string $method, array $arguments): MethodCall
    {
        if (is_string($variable)) {
            $variable = new Variable($variable);
        }

        if ($variable instanceof PropertyFetch) {
            $variable = new PropertyFetch($variable->var, $variable->name);
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

    /**
     * @param mixed $item
     * @param string|int|null $key
     */
    private function createArrayItem($item, $key = null): ArrayItem
    {
        $arrayItem = null;

        if ($item instanceof Variable
            || $item instanceof String_
            || $item instanceof MethodCall
            || $item instanceof StaticCall
            || $item instanceof Expr\FuncCall
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

        throw new NotImplementedException(sprintf(
            'Not implemented yet. Go to "%s()" and add check for "%s" node.',
            __METHOD__,
            is_object($item) ? get_class($item) : $item
        ));
    }

    private function createVarDoc(Type $type): Doc
    {
        $docString = $this->staticTypeMapper->mapPHPStanTypeToDocString($type);

        return new Doc(sprintf('/**%s * @var %s%s */', PHP_EOL, $docString, PHP_EOL));
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
}
