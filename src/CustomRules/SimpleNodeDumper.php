<?php

declare (strict_types=1);
namespace Rector\CustomRules;

use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
/**
 * Inspired by @see \PhpParser\NodeDumper
 */
final class SimpleNodeDumper
{
    /**
     * @param Node[]|Node|mixed[] $node
     */
    public static function dump($node, bool $rootNode = \true) : string
    {
        // display single root node directly to avoid useless nesting in output
        if (\is_array($node) && \count($node) === 1 && $rootNode) {
            $node = $node[0];
        }
        if ($node instanceof Node) {
            return self::dumpSingleNode($node);
        }
        if (self::isStringList($node)) {
            return \json_encode($node, 0);
        }
        $result = '[';
        foreach ($node as $key => $value) {
            $result .= "\n    " . $key . ': ';
            if ($value === null) {
                $result .= 'null';
            } elseif ($value === \false) {
                $result .= 'false';
            } elseif ($value === \true) {
                $result .= 'true';
            } elseif (\is_string($value)) {
                $result .= '"' . $value . '"';
            } elseif (\is_scalar($value)) {
                $result .= $value;
            } else {
                $result .= \str_replace("\n", "\n    ", self::dump($value, \false));
            }
        }
        if (\count($node) === 0) {
            $result .= ']';
        } else {
            $result .= "\n]";
        }
        return $result;
    }
    /**
     * @param mixed[] $items
     */
    private static function isStringList(array $items) : bool
    {
        foreach ($items as $item) {
            if (!\is_string($item)) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @param mixed $flags
     */
    private static function dumpFlags($flags) : string
    {
        $strs = [];
        if (($flags & Class_::MODIFIER_PUBLIC) !== 0) {
            $strs[] = 'MODIFIER_PUBLIC';
        }
        if (($flags & Class_::MODIFIER_PROTECTED) !== 0) {
            $strs[] = 'MODIFIER_PROTECTED';
        }
        if (($flags & Class_::MODIFIER_PRIVATE) !== 0) {
            $strs[] = 'MODIFIER_PRIVATE';
        }
        if (($flags & Class_::MODIFIER_ABSTRACT) !== 0) {
            $strs[] = 'MODIFIER_ABSTRACT';
        }
        if (($flags & Class_::MODIFIER_STATIC) !== 0) {
            $strs[] = 'MODIFIER_STATIC';
        }
        if (($flags & Class_::MODIFIER_FINAL) !== 0) {
            $strs[] = 'MODIFIER_FINAL';
        }
        if (($flags & Class_::MODIFIER_READONLY) !== 0) {
            $strs[] = 'MODIFIER_READONLY';
        }
        if ($strs !== []) {
            return \implode(' | ', $strs) . ' (' . $flags . ')';
        }
        return (string) $flags;
    }
    /**
     * @param int|float|string $type
     */
    private static function dumpIncludeType($type) : string
    {
        $map = [Include_::TYPE_INCLUDE => 'TYPE_INCLUDE', Include_::TYPE_INCLUDE_ONCE => 'TYPE_INCLUDE_ONCE', Include_::TYPE_REQUIRE => 'TYPE_REQUIRE', Include_::TYPE_REQUIRE_ONCE => 'TYPE_REQUIRE_ONCE'];
        if (!isset($map[$type])) {
            return (string) $type;
        }
        return $map[$type] . ' (' . $type . ')';
    }
    /**
     * @param mixed $type
     */
    private static function dumpUseType($type) : string
    {
        $map = [Use_::TYPE_UNKNOWN => 'TYPE_UNKNOWN', Use_::TYPE_NORMAL => 'TYPE_NORMAL', Use_::TYPE_FUNCTION => 'TYPE_FUNCTION', Use_::TYPE_CONSTANT => 'TYPE_CONSTANT'];
        if (!isset($map[$type])) {
            return (string) $type;
        }
        return $map[$type] . ' (' . $type . ')';
    }
    private static function dumpSingleNode(Node $node) : string
    {
        $result = \get_class($node);
        // print simple nodes on same line, to make output more readable
        if ($node instanceof Variable && \is_string($node->name)) {
            $result .= '( name: "' . $node->name . '" )';
        } elseif ($node instanceof Identifier) {
            $result .= '( name: "' . $node->name . '" )';
        } elseif ($node instanceof Name) {
            $result .= '( parts: ' . \json_encode($node->getParts(), 0) . ' )';
        } elseif ($node instanceof Scalar && $node->getSubNodeNames() === ['value']) {
            if (\is_string($node->value)) {
                $result .= '( value: "' . $node->value . '" )';
            } else {
                $result .= '( value: ' . $node->value . ' )';
            }
        } else {
            $result .= '(';
            foreach ($node->getSubNodeNames() as $key) {
                $result .= "\n    " . $key . ': ';
                $value = $node->{$key};
                if ($value === null) {
                    $result .= 'null';
                } elseif ($value === \false) {
                    $result .= 'false';
                } elseif ($value === \true) {
                    $result .= 'true';
                } elseif (\is_scalar($value)) {
                    if ($key === 'flags' || $key === 'newModifier') {
                        $result .= self::dumpFlags($value);
                    } elseif ($key === 'type' && $node instanceof Include_) {
                        $result .= self::dumpIncludeType($value);
                    } elseif ($key === 'type' && ($node instanceof Use_ || $node instanceof UseUse || $node instanceof GroupUse)) {
                        $result .= self::dumpUseType($value);
                    } elseif (\is_string($value)) {
                        $result .= '"' . $value . '"';
                    } else {
                        $result .= $value;
                    }
                } else {
                    $result .= \str_replace("\n", "\n    ", self::dump($value, \false));
                }
            }
            $result .= "\n)";
        }
        return $result;
    }
}
