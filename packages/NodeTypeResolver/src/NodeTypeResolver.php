<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
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
        foreach ($perNodeTypeResolver->getNodeClasses() as $nodeClass) {
            $this->perNodeTypeResolvers[$nodeClass] = $perNodeTypeResolver;
        }
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        // @todo some cache per object hash?

        $nodeClass = get_class($node);
        if (! isset($this->perNodeTypeResolvers[$nodeClass])) {
            return [];
        }

        $nodeTypes = $this->perNodeTypeResolvers[$nodeClass]->resolve($node);

        return $this->cleanPreSlashes($nodeTypes);
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
