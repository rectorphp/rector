<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
/**
 * Read-only utils for ClassConstAnalyzer Node:
 * "false, true..."
 */
final class ConstFetchAnalyzer
{
    public function isTrueOrFalse(Node $node) : bool
    {
        if ($this->isTrue($node)) {
            return \true;
        }
        return $this->isFalse($node);
    }
    public function isFalse(Node $node) : bool
    {
        return $this->isConstantWithLowercasedName($node, 'false');
    }
    public function isTrue(Node $node) : bool
    {
        return $this->isConstantWithLowercasedName($node, 'true');
    }
    public function isNull(Node $node) : bool
    {
        return $this->isConstantWithLowercasedName($node, 'null');
    }
    private function isConstantWithLowercasedName(Node $node, string $name) : bool
    {
        if (!$node instanceof ConstFetch) {
            return \false;
        }
        return $node->name->toLowerString() === $name;
    }
}
