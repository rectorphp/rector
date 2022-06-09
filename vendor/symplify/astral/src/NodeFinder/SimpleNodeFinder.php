<?php

declare (strict_types=1);
namespace RectorPrefix20220609\Symplify\Astral\NodeFinder;

use PhpParser\Node;
use PhpParser\NodeFinder;
use RectorPrefix20220609\Symplify\Astral\ValueObject\AttributeKey;
final class SimpleNodeFinder
{
    /**
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    public function __construct(NodeFinder $nodeFinder)
    {
        $this->nodeFinder = $nodeFinder;
    }
    /**
     * @template T of Node
     * @param class-string<T> $nodeClass
     * @return \PhpParser\Node|null
     */
    public function findFirstByType(Node $node, string $nodeClass)
    {
        return $this->nodeFinder->findFirstInstanceOf($node, $nodeClass);
    }
    /**
     * @template T of Node
     * @param class-string<T> $nodeClass
     * @return T[]
     */
    public function findByType(Node $node, string $nodeClass) : array
    {
        return $this->nodeFinder->findInstanceOf($node, $nodeClass);
    }
    /**
     * @template T of Node
     * @param array<class-string<T>> $nodeClasses
     */
    public function hasByTypes(Node $node, array $nodeClasses) : bool
    {
        foreach ($nodeClasses as $nodeClass) {
            $foundNodes = $this->findByType($node, $nodeClass);
            if ($foundNodes !== []) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @see https://phpstan.org/blog/generics-in-php-using-phpdocs for template
     *
     * @template T of Node
     * @param class-string<T> $nodeClass
     * @return T|null
     */
    public function findFirstParentByType(Node $node, string $nodeClass) : ?Node
    {
        $node = $node->getAttribute(AttributeKey::PARENT);
        while ($node instanceof Node) {
            if (\is_a($node, $nodeClass, \true)) {
                return $node;
            }
            $node = $node->getAttribute(AttributeKey::PARENT);
        }
        return null;
    }
    /**
     * @template T of Node
     * @param array<class-string<T>&class-string<Node>> $nodeTypes
     * @return T|null
     */
    public function findFirstParentByTypes(Node $node, array $nodeTypes) : ?Node
    {
        $node = $node->getAttribute(AttributeKey::PARENT);
        while ($node instanceof Node) {
            foreach ($nodeTypes as $nodeType) {
                if (\is_a($node, $nodeType)) {
                    return $node;
                }
            }
            $node = $node->getAttribute(AttributeKey::PARENT);
        }
        return null;
    }
}
