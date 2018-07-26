<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use Rector\Exception\ShouldNotHappenException;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeVisitor\PHPStanScopeNodeVisitor;

final class NodeTypeResolver
{
    /**
     * @var PerNodeTypeResolverInterface[]
     */
    private $perNodeTypeResolvers = [];

    public function addPerNodeTypeResolver(PerNodeTypeResolverInterface $perNodeTypeResolver): void
    {
        foreach ($perNodeTypeResolver->getNodeClasses() as $nodeClass) {
            $this->perNodeTypeResolvers[$nodeClass] = $perNodeTypeResolver;
        }
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        if (! $node->hasAttribute(Attribute::SCOPE)) {
            throw new ShouldNotHappenException(sprintf(
                'The "%s" Node attribute should be resolved by "%s" in previous run.',
                Attribute::SCOPE,
                PHPStanScopeNodeVisitor::class
            ));
        }

        // resolve just once
        if ($node->hasAttribute(Attribute::TYPES)) {
            return $node->getAttribute(Attribute::TYPES);
        }

        $nodeClass = get_class($node);
        if (! isset($this->perNodeTypeResolvers[$nodeClass])) {
            return [];
        }

        $nodeTypes = $this->perNodeTypeResolvers[$nodeClass]->resolve($node);
        $nodeTypes = $this->cleanPreSlashes($nodeTypes);

        $node->setAttribute(Attribute::TYPES, $nodeTypes);

        return $nodeTypes;
    }

    /**
     * "\FqnType" => "FqnType"
     *
     * @param string[] $nodeTypes
     * @return string[]
     */
    private function cleanPreSlashes(array $nodeTypes): array
    {
        foreach ($nodeTypes as $key => $nodeType) {
            // filter out non-type values
            if (! is_string($nodeType)) {
                continue;
            }
            $nodeTypes[$key] = ltrim($nodeType, '\\');
        }

        return $nodeTypes;
    }
}
