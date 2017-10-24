<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Scalar\MagicConst\Namespace_;
use Rector\Node\Attribute;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;

final class NamespaceConstantValueResolver implements PerNodeValueResolverInterface
{
    public function getNodeClass(): string
    {
        return Namespace_::class;
    }

    public function resolve(Node $arrayNode): string
    {
        return (string) $arrayNode->getAttribute(Attribute::NAMESPACE_NAME);
    }
}
