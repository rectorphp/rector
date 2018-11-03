<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use Rector\Node\NodeFactory;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeFactoryTrait
{
    /**
     * @var NodeFactory
     */
    public $nodeFactory;

    /**
     * @required
     */
    public function autowireNodeFactoryTrait(NodeFactory $nodeFactory): void
    {
        $this->nodeFactory = $nodeFactory;
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

    protected function createPropertyAssignmentWithExpr(string $propertyName, Expr $rightExprNode): Expression
    {
        return $this->nodeFactory->createPropertyAssignmentWithExpr($propertyName, $rightExprNode);
    }
}
