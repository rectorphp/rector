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
use Rector\Exception\NotImplementedException;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Php\TypeAnalyzer;

final class NodeFactory
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    public function __construct(BuilderFactory $builderFactory, TypeAnalyzer $typeAnalyzer)
    {
        $this->builderFactory = $builderFactory;
        $this->typeAnalyzer = $typeAnalyzer;
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
        $classConstFetchNode->class->setAttribute(Attribute::RESOLVED_NAME, $classNameNode);

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

        foreach ($items as $item) {
            $arrayItems[] = $this->createArrayItem($item);
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
        return $this->builderFactory->method($name)
            ->makePublic()
            ->getNode();
    }

    public function createParamFromVariableInfo(VariableInfo $variableInfo): Param
    {
        $paramBuild = $this->builderFactory->param($variableInfo->getName());
        $paramBuild->setType($this->createTypeName($variableInfo->getType()));

        return $paramBuild->getNode();
    }

    public function createPrivatePropertyFromVariableInfo(VariableInfo $variableInfo): Property
    {
        $docComment = $this->createVarDoc($variableInfo->getType());

        $propertyBuilder = $this->builderFactory->property($variableInfo->getName())
            ->makePrivate()
            ->setDocComment($docComment);

        return $propertyBuilder->getNode();
    }

    public function createTypeName(string $name): Name
    {
        if ($this->typeAnalyzer->isPhpReservedType($name)) {
            return new Name($name);
        }

        return new FullyQualified($name);
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

        $variable->setAttribute(Attribute::PARENT_NODE, $methodCallNode);

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
     */
    private function createArrayItem($item): ArrayItem
    {
        if ($item instanceof Variable) {
            return new ArrayItem($item);
        }

        if ($item instanceof Identifier) {
            $string = new String_($item->toString());
            return new ArrayItem($string);
        }

        if (is_scalar($item)) {
            return new ArrayItem(BuilderHelpers::normalizeValue($item));
        }

        throw new NotImplementedException(sprintf(
            'Not implemented yet. Go to "%s()" and add check for "%s" node.',
            __METHOD__,
            get_class($item)
        ));
    }

    private function createVarDoc(string $type): Doc
    {
        $type = $this->typeAnalyzer->isPhpReservedType($type) ? $type : '\\' . $type;
        return new Doc(sprintf('/**%s * @var %s%s */', PHP_EOL, $type, PHP_EOL));
    }

    /**
     * @param Param[] $paramNodes
     * @return Arg[]
     */
    private function convertParamNodesToArgNodes(array $paramNodes): array
    {
        $argNodes = [];
        foreach ($paramNodes as $paramNode) {
            $argNodes[] = new Arg($paramNode->var);
        }

        return $argNodes;
    }
}
