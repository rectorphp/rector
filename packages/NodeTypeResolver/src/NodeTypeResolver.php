<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class NodeTypeResolver
{
    /**
     * @var PerNodeTypeResolverInterface[]
     */
    private $perNodeTypeResolvers = [];

    /**
     * @var string[][]
     */
    private $resolvedTypesByNode = [];

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
        $nodeClass = get_class($node);

        if (! isset($this->perNodeTypeResolvers[$nodeClass])) {
            return [];
        }

        $key = spl_object_hash($node);

        if (! isset($this->resolvedTypesByNode[$key])) {
            $types = $this->perNodeTypeResolvers[$nodeClass]->resolve($node);
            $types = $this->cleanPreSlashes($types);
            $this->resolvedTypesByNode[$key] = $types;
        }

        return $this->resolvedTypesByNode[$key];

//        // @todo move to local cache, so there is only one way to get such types
//        // and that cache can be outsourced to persistent storage
//
//        // resolve just once
////        if ($node->hasAttribute(Attribute::TYPES)) {
////            return $node->getAttribute(Attribute::TYPES);
////        }
//
//        $nodeTypes = $this->perNodeTypeResolvers[$nodeClass]->resolve($node);
//        $nodeTypes = $this->cleanPreSlashes($nodeTypes);
//
//        // $node->setAttribute(Attribute::TYPES, $nodeTypes);
//
//        return $nodeTypes;
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
