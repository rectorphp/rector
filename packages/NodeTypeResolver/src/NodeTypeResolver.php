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

    public function addPerNodeTypeResolver(PerNodeTypeResolverInterface $perNodeTypeResolver): void
    {
        $this->perNodeTypeResolvers[$perNodeTypeResolver->getNodeTypes()] = $perNodeTypeResolver;
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        // @todo isset

        foreach ($this->perNodeTypeResolvers as $class => $perNodeTypeResolver) {

            dump($node->getType());

            die;

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
