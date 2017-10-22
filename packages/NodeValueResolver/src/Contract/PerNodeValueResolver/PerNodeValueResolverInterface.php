<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\Contract\PerNodeValueResolver;

use PhpParser\Node;

interface PerNodeValueResolverInterface
{
    public function getNodeClass(): string;

    /**
     * @return mixed
     */
    public function resolve(Node $node);
}
