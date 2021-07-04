<?php

declare (strict_types=1);
namespace RectorPrefix20210704\Symplify\SimplePhpDocParser;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use RectorPrefix20210704\Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface;
use RectorPrefix20210704\Symplify\SimplePhpDocParser\Exception\InvalidTraverseException;
use RectorPrefix20210704\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\CallablePhpDocNodeVisitor;
/**
 * Mimics
 * https://github.com/nikic/PHP-Parser/blob/4abdcde5f16269959a834e4e58ea0ba0938ab133/lib/PhpParser/NodeTraverser.php
 *
 * @see \Symplify\SimplePhpDocParser\Tests\SimplePhpDocNodeTraverser\PhpDocNodeTraverserTest
 */
final class PhpDocNodeTraverser
{
    /**
     * Return from enterNode() to remove node from the tree
     *
     * @var int
     */
    public const NODE_REMOVE = 1;
    /**
     * @var PhpDocNodeVisitorInterface[]
     */
    private $phpDocNodeVisitors = [];
    public function addPhpDocNodeVisitor(\RectorPrefix20210704\Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface $phpDocNodeVisitor) : void
    {
        $this->phpDocNodeVisitors[] = $phpDocNodeVisitor;
    }
    public function traverse(\PHPStan\PhpDocParser\Ast\Node $node) : void
    {
        foreach ($this->phpDocNodeVisitors as $phpDocNodeVisitor) {
            $phpDocNodeVisitor->beforeTraverse($node);
        }
        $node = $this->traverseNode($node);
        if (\is_int($node)) {
            throw new \RectorPrefix20210704\Symplify\SimplePhpDocParser\Exception\InvalidTraverseException();
        }
        foreach ($this->phpDocNodeVisitors as $phpDocNodeVisitor) {
            $phpDocNodeVisitor->afterTraverse($node);
        }
    }
    public function traverseWithCallable(\PHPStan\PhpDocParser\Ast\Node $node, string $docContent, callable $callable) : \PHPStan\PhpDocParser\Ast\Node
    {
        $callablePhpDocNodeVisitor = new \RectorPrefix20210704\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\CallablePhpDocNodeVisitor($callable, $docContent);
        $this->addPhpDocNodeVisitor($callablePhpDocNodeVisitor);
        $this->traverse($node);
        return $node;
    }
    /**
     * @template TNode of Node
     * @param TNode $node
     * @return \PHPStan\PhpDocParser\Ast\Node|int
     */
    private function traverseNode(\PHPStan\PhpDocParser\Ast\Node $node)
    {
        $subNodeNames = \array_keys(\get_object_vars($node));
        foreach ($subNodeNames as $subNodeName) {
            $subNode =& $node->{$subNodeName};
            if (\is_array($subNode)) {
                $subNode = $this->traverseArray($subNode);
            } elseif ($subNode instanceof \PHPStan\PhpDocParser\Ast\Node) {
                foreach ($this->phpDocNodeVisitors as $phpDocNodeVisitor) {
                    $return = $phpDocNodeVisitor->enterNode($subNode);
                    if ($return instanceof \PHPStan\PhpDocParser\Ast\Node) {
                        $subNode = $return;
                    } elseif ($return === self::NODE_REMOVE) {
                        if ($subNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode) {
                            // we have to remove the node above
                            return self::NODE_REMOVE;
                        }
                        $subNode = null;
                        continue 2;
                    }
                }
                $subNode = $this->traverseNode($subNode);
                if (\is_int($subNode)) {
                    throw new \RectorPrefix20210704\Symplify\SimplePhpDocParser\Exception\InvalidTraverseException();
                }
                foreach ($this->phpDocNodeVisitors as $phpDocNodeVisitor) {
                    $phpDocNodeVisitor->leaveNode($subNode);
                }
            }
        }
        return $node;
    }
    /**
     * @param array<Node|mixed> $nodes
     * @return array<Node|mixed>
     */
    private function traverseArray(array $nodes) : array
    {
        foreach ($nodes as $key => &$node) {
            // can be string or something else
            if (!$node instanceof \PHPStan\PhpDocParser\Ast\Node) {
                continue;
            }
            foreach ($this->phpDocNodeVisitors as $phpDocNodeVisitor) {
                $return = $phpDocNodeVisitor->enterNode($node);
                if ($return instanceof \PHPStan\PhpDocParser\Ast\Node) {
                    $node = $return;
                } elseif ($return === self::NODE_REMOVE) {
                    // remove node
                    unset($nodes[$key]);
                    continue 2;
                }
            }
            $return = $this->traverseNode($node);
            // remove value node
            if ($return === self::NODE_REMOVE) {
                unset($nodes[$key]);
                continue;
            }
            if (\is_int($return)) {
                throw new \RectorPrefix20210704\Symplify\SimplePhpDocParser\Exception\InvalidTraverseException();
            }
            $node = $return;
            foreach ($this->phpDocNodeVisitors as $phpDocNodeVisitor) {
                $phpDocNodeVisitor->leaveNode($node);
            }
        }
        return $nodes;
    }
}
