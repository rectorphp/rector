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

    /**
     * @param Namespace_ $namespaceNode
     */
    public function resolve(Node $namespaceNode): string
    {
        return (string) $namespaceNode->getAttribute(Attribute::NAMESPACE_NAME);
    }
}
