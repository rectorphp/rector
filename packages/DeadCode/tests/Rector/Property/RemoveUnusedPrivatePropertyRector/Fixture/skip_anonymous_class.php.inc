<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

class SkipAnonymousClass
{
    public function createNodeVisitor(callable $callable): NodeVisitor
    {
        return new class($callable) extends NodeVisitorAbstract {
            /**
             * @var callable
             */
            private $callable;

            public function __construct(callable $callable)
            {
                $this->callable = $callable;
            }

            /**
             * @return int|Node|null
             */
            public function enterNode(Node $node)
            {
                $callable = $this->callable;
                return $callable($node);
            }
        };
    }
}
