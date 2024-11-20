<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast;

use LogicException;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function array_keys;
use function array_pop;
use function array_splice;
use function count;
use function get_class;
use function get_object_vars;
use function gettype;
use function is_array;
use function sprintf;
/**
 * Inspired by https://github.com/nikic/PHP-Parser/tree/36a6dcd04e7b0285e8f0868f44bd4927802f7df1
 *
 * Copyright (c) 2011, Nikita Popov
 * All rights reserved.
 */
final class NodeTraverser
{
    /**
     * If NodeVisitor::enterNode() returns DONT_TRAVERSE_CHILDREN, child nodes
     * of the current node will not be traversed for any visitors.
     *
     * For subsequent visitors enterNode() will still be called on the current
     * node and leaveNode() will also be invoked for the current node.
     */
    public const DONT_TRAVERSE_CHILDREN = 1;
    /**
     * If NodeVisitor::enterNode() or NodeVisitor::leaveNode() returns
     * STOP_TRAVERSAL, traversal is aborted.
     *
     * The afterTraverse() method will still be invoked.
     */
    public const STOP_TRAVERSAL = 2;
    /**
     * If NodeVisitor::leaveNode() returns REMOVE_NODE for a node that occurs
     * in an array, it will be removed from the array.
     *
     * For subsequent visitors leaveNode() will still be invoked for the
     * removed node.
     */
    public const REMOVE_NODE = 3;
    /**
     * If NodeVisitor::enterNode() returns DONT_TRAVERSE_CURRENT_AND_CHILDREN, child nodes
     * of the current node will not be traversed for any visitors.
     *
     * For subsequent visitors enterNode() will not be called as well.
     * leaveNode() will be invoked for visitors that has enterNode() method invoked.
     */
    public const DONT_TRAVERSE_CURRENT_AND_CHILDREN = 4;
    /** @var list<NodeVisitor> Visitors */
    private array $visitors = [];
    /** @var bool Whether traversal should be stopped */
    private bool $stopTraversal;
    /**
     * @param list<NodeVisitor> $visitors
     */
    public function __construct(array $visitors)
    {
        $this->visitors = $visitors;
    }
    /**
     * Traverses an array of nodes using the registered visitors.
     *
     * @param Node[] $nodes Array of nodes
     *
     * @return Node[] Traversed array of nodes
     */
    public function traverse(array $nodes) : array
    {
        $this->stopTraversal = \false;
        foreach ($this->visitors as $visitor) {
            $return = $visitor->beforeTraverse($nodes);
            if ($return === null) {
                continue;
            }
            $nodes = $return;
        }
        $nodes = $this->traverseArray($nodes);
        foreach ($this->visitors as $visitor) {
            $return = $visitor->afterTraverse($nodes);
            if ($return === null) {
                continue;
            }
            $nodes = $return;
        }
        return $nodes;
    }
    /**
     * Recursively traverse a node.
     *
     * @param Node $node Node to traverse.
     *
     * @return Node Result of traversal (may be original node or new one)
     */
    private function traverseNode(\PHPStan\PhpDocParser\Ast\Node $node) : \PHPStan\PhpDocParser\Ast\Node
    {
        $subNodeNames = array_keys(get_object_vars($node));
        foreach ($subNodeNames as $name) {
            $subNode =& $node->{$name};
            if (is_array($subNode)) {
                $subNode = $this->traverseArray($subNode);
                if ($this->stopTraversal) {
                    break;
                }
            } elseif ($subNode instanceof \PHPStan\PhpDocParser\Ast\Node) {
                $traverseChildren = \true;
                $breakVisitorIndex = null;
                foreach ($this->visitors as $visitorIndex => $visitor) {
                    $return = $visitor->enterNode($subNode);
                    if ($return === null) {
                        continue;
                    }
                    if ($return instanceof \PHPStan\PhpDocParser\Ast\Node) {
                        $this->ensureReplacementReasonable($subNode, $return);
                        $subNode = $return;
                    } elseif ($return === self::DONT_TRAVERSE_CHILDREN) {
                        $traverseChildren = \false;
                    } elseif ($return === self::DONT_TRAVERSE_CURRENT_AND_CHILDREN) {
                        $traverseChildren = \false;
                        $breakVisitorIndex = $visitorIndex;
                        break;
                    } elseif ($return === self::STOP_TRAVERSAL) {
                        $this->stopTraversal = \true;
                        break 2;
                    } else {
                        throw new LogicException('enterNode() returned invalid value of type ' . gettype($return));
                    }
                }
                if ($traverseChildren) {
                    $subNode = $this->traverseNode($subNode);
                    if ($this->stopTraversal) {
                        break;
                    }
                }
                foreach ($this->visitors as $visitorIndex => $visitor) {
                    $return = $visitor->leaveNode($subNode);
                    if ($return !== null) {
                        if ($return instanceof \PHPStan\PhpDocParser\Ast\Node) {
                            $this->ensureReplacementReasonable($subNode, $return);
                            $subNode = $return;
                        } elseif ($return === self::STOP_TRAVERSAL) {
                            $this->stopTraversal = \true;
                            break 2;
                        } elseif (is_array($return)) {
                            throw new LogicException('leaveNode() may only return an array ' . 'if the parent structure is an array');
                        } else {
                            throw new LogicException('leaveNode() returned invalid value of type ' . gettype($return));
                        }
                    }
                    if ($breakVisitorIndex === $visitorIndex) {
                        break;
                    }
                }
            }
        }
        return $node;
    }
    /**
     * Recursively traverse array (usually of nodes).
     *
     * @param mixed[] $nodes Array to traverse
     *
     * @return mixed[] Result of traversal (may be original array or changed one)
     */
    private function traverseArray(array $nodes) : array
    {
        $doNodes = [];
        foreach ($nodes as $i => &$node) {
            if ($node instanceof \PHPStan\PhpDocParser\Ast\Node) {
                $traverseChildren = \true;
                $breakVisitorIndex = null;
                foreach ($this->visitors as $visitorIndex => $visitor) {
                    $return = $visitor->enterNode($node);
                    if ($return === null) {
                        continue;
                    }
                    if ($return instanceof \PHPStan\PhpDocParser\Ast\Node) {
                        $this->ensureReplacementReasonable($node, $return);
                        $node = $return;
                    } elseif (is_array($return)) {
                        $doNodes[] = [$i, $return];
                        continue 2;
                    } elseif ($return === self::REMOVE_NODE) {
                        $doNodes[] = [$i, []];
                        continue 2;
                    } elseif ($return === self::DONT_TRAVERSE_CHILDREN) {
                        $traverseChildren = \false;
                    } elseif ($return === self::DONT_TRAVERSE_CURRENT_AND_CHILDREN) {
                        $traverseChildren = \false;
                        $breakVisitorIndex = $visitorIndex;
                        break;
                    } elseif ($return === self::STOP_TRAVERSAL) {
                        $this->stopTraversal = \true;
                        break 2;
                    } else {
                        throw new LogicException('enterNode() returned invalid value of type ' . gettype($return));
                    }
                }
                if ($traverseChildren) {
                    $node = $this->traverseNode($node);
                    if ($this->stopTraversal) {
                        break;
                    }
                }
                foreach ($this->visitors as $visitorIndex => $visitor) {
                    $return = $visitor->leaveNode($node);
                    if ($return !== null) {
                        if ($return instanceof \PHPStan\PhpDocParser\Ast\Node) {
                            $this->ensureReplacementReasonable($node, $return);
                            $node = $return;
                        } elseif (is_array($return)) {
                            $doNodes[] = [$i, $return];
                            break;
                        } elseif ($return === self::REMOVE_NODE) {
                            $doNodes[] = [$i, []];
                            break;
                        } elseif ($return === self::STOP_TRAVERSAL) {
                            $this->stopTraversal = \true;
                            break 2;
                        } else {
                            throw new LogicException('leaveNode() returned invalid value of type ' . gettype($return));
                        }
                    }
                    if ($breakVisitorIndex === $visitorIndex) {
                        break;
                    }
                }
            } elseif (is_array($node)) {
                throw new LogicException('Invalid node structure: Contains nested arrays');
            }
        }
        if (count($doNodes) > 0) {
            while ([$i, $replace] = array_pop($doNodes)) {
                array_splice($nodes, $i, 1, $replace);
            }
        }
        return $nodes;
    }
    private function ensureReplacementReasonable(\PHPStan\PhpDocParser\Ast\Node $old, \PHPStan\PhpDocParser\Ast\Node $new) : void
    {
        if ($old instanceof TypeNode && !$new instanceof TypeNode) {
            throw new LogicException(sprintf('Trying to replace TypeNode with %s', get_class($new)));
        }
        if ($old instanceof ConstExprNode && !$new instanceof ConstExprNode) {
            throw new LogicException(sprintf('Trying to replace ConstExprNode with %s', get_class($new)));
        }
        if ($old instanceof PhpDocChildNode && !$new instanceof PhpDocChildNode) {
            throw new LogicException(sprintf('Trying to replace PhpDocChildNode with %s', get_class($new)));
        }
        if ($old instanceof PhpDocTagValueNode && !$new instanceof PhpDocTagValueNode) {
            throw new LogicException(sprintf('Trying to replace PhpDocTagValueNode with %s', get_class($new)));
        }
    }
}
