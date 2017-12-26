<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class NodeTypeResolver
{
    /**
     * @var PerNodeTypeResolverInterface[]
     */
    private $perNodeTypeResolvers = [];

    /**
     * @var string[]
     */
    private $nodesToSkip = [
        Namespace_::class,
        Use_::class,
        UseUse::class,
    ];

    public function addPerNodeTypeResolver(PerNodeTypeResolverInterface $perNodeTypeResolver): void
    {
        $this->perNodeTypeResolvers[$perNodeTypeResolver->getNodeClass()] = $perNodeTypeResolver;
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        if (in_array(get_class($node), $this->nodesToSkip, true)) {
            return [];
        }

        foreach ($this->perNodeTypeResolvers as $class => $perNodeTypeResolver) {
            if (! $node instanceof $class) {
                continue;
            }

            // resolve just once
            if ($node->getAttribute(Attribute::TYPES)) {
                return $node->getAttribute(Attribute::TYPES);
            }

            return $perNodeTypeResolver->resolve($node);
        }

        return [];
    }
}
