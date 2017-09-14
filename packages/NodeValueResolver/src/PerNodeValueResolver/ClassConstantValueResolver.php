<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use Rector\Node\Attribute;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;

final class ClassConstantValueResolver implements PerNodeValueResolverInterface
{
    public function getNodeClass(): string
    {
        return Node\Scalar\MagicConst\Class_::class;
    }
    public function resolve(Node $node): string
    {
        return (string) $node->getAttribute(Attribute::CLASS_NAME);
    }
}
