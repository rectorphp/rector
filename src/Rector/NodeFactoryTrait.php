<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
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
    private $nodeFactory;

    /**
     * @required
     */
    public function autowireNodeFactoryTrait(NodeFactory $nodeFactory): void
    {
        $this->nodeFactory = $nodeFactory;
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

    protected function createArg(Node $node): Arg
    {
        return $this->nodeFactory->createArg($node);
    }

    /**
     * @param Node[] $nodes
     * @return Arg[]
     */
    protected function createArgs(array $nodes): array
    {
        return $this->nodeFactory->createArgs($nodes);
    }

    /**
     * @param mixed[] $arguments
     */
    protected function createFunction(string $name, array $arguments = []): FuncCall
    {
        return new FuncCall(new Name($name), $arguments);
    }
}
