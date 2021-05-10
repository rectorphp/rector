<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
/**
 * Read-only utils for ClassConstAnalyzer Node:
 * "false, true..."
 */
final class ConstFetchAnalyzer
{
    public function isTrueOrFalse(\PhpParser\Node $node) : bool
    {
        if ($this->isTrue($node)) {
            return \true;
        }
        return $this->isFalse($node);
    }
    public function isFalse(\PhpParser\Node $node) : bool
    {
        return $this->isConstantWithLowercasedName($node, 'false');
    }
    public function isTrue(\PhpParser\Node $node) : bool
    {
        return $this->isConstantWithLowercasedName($node, 'true');
    }
    public function isNull(\PhpParser\Node $node) : bool
    {
        return $this->isConstantWithLowercasedName($node, 'null');
    }
    private function isConstantWithLowercasedName(\PhpParser\Node $node, string $name) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\ConstFetch) {
            return \false;
        }
        return $node->name->toLowerString() === $name;
    }
}
