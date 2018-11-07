<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name;
use Rector\PhpParser\Node\NodeFactory;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeFactoryTrait
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @required
     */
    public function autowireNodeFactoryTrait(NodeFactory $nodeFactory, BuilderFactory $builderFactory): void
    {
        $this->nodeFactory = $nodeFactory;
        $this->builderFactory = $builderFactory;
    }

    public function createClassConstant(string $class, string $constant): ClassConstFetch
    {
        return $this->nodeFactory->createClassConstant($class, $constant);
    }

    protected function createNull(): ConstFetch
    {
        return new ConstFetch(new Name('null'));
    }

    protected function createFalse(): ConstFetch
    {
        return new ConstFetch(new Name('false'));
    }

    protected function createTrue(): ConstFetch
    {
        return new ConstFetch(new Name('true'));
    }

    /**
     * @param mixed $argument
     */
    protected function createArg($argument): Arg
    {
        return $this->nodeFactory->createArg($argument);
    }

    /**
     * @param mixed[] $arguments
     * @return Arg[]
     */
    protected function createArgs(array $arguments): array
    {
        return $this->nodeFactory->createArgs($arguments);
    }

    /**
     * @param Node[] $nodes
     */
    protected function createArray(array $nodes): Array_
    {
        return $this->nodeFactory->createArray($nodes);
    }

    /**
     * @param mixed[] $arguments
     */
    protected function createFunction(string $name, array $arguments = []): FuncCall
    {
        return new FuncCall(new Name($name), $arguments);
    }

    protected function createClassConstantReference(string $class): ClassConstFetch
    {
        return $this->nodeFactory->createClassConstantReference($class);
    }

    protected function createPropertyAssignmentWithExpr(string $propertyName, Expr $rightExprNode): Assign
    {
        return $this->nodeFactory->createPropertyAssignmentWithExpr($propertyName, $rightExprNode);
    }

    /**
     * @param string|Expr $variable
     * @param mixed[] $arguments
     */
    protected function createMethodCall($variable, string $method, array $arguments = []): MethodCall
    {
        return $this->nodeFactory->createMethodCall($variable, $method, $arguments);
    }

    /**
     * @param string|Expr $variable
     */
    protected function createPropertyFetch($variable, string $property): PropertyFetch
    {
        return $this->nodeFactory->createPropertyFetch($variable, $property);
    }
}
