<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\Astral\NodeFinder;

use PhpParser\Node;
use PhpParser\NodeFinder;
use RectorPrefix20210510\Symplify\Astral\ValueObject\CommonAttributeKey;
use RectorPrefix20210510\Symplify\PackageBuilder\Php\TypeChecker;
final class SimpleNodeFinder
{
    /**
     * @var TypeChecker
     */
    private $typeChecker;
    /**
     * @var NodeFinder
     */
    private $nodeFinder;
    public function __construct(TypeChecker $typeChecker, NodeFinder $nodeFinder)
    {
        $this->typeChecker = $typeChecker;
        $this->nodeFinder = $nodeFinder;
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
     * @see https://phpstan.org/blog/generics-in-php-using-phpdocs for template
     *
     * @template T of Node
     * @param class-string<T> $nodeClass
     * @return T|null
     */
    public function findFirstParentByType(Node $node, string $nodeClass) : ?Node
    {
        $node = $node->getAttribute(CommonAttributeKey::PARENT);
        while ($node) {
            if (\is_a($node, $nodeClass, \true)) {
                return $node;
            }
            $node = $node->getAttribute(CommonAttributeKey::PARENT);
        }
        return null;
    }
    /**
     * @template T of Node
     * @param class-string<T>[] $nodeTypes
     * @return T|null
     */
    public function findFirstParentByTypes(Node $node, array $nodeTypes) : ?Node
    {
        $node = $node->getAttribute(CommonAttributeKey::PARENT);
        while ($node) {
            if ($this->typeChecker->isInstanceOf($node, $nodeTypes)) {
                return $node;
            }
            $node = $node->getAttribute(CommonAttributeKey::PARENT);
        }
        return null;
    }
}
