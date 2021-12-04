<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\VariadicPlaceholder;
use Rector\NodeNameResolver\NodeNameResolver;
final class CompactFuncCallAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isInCompact(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        if (!$this->nodeNameResolver->isName($funcCall, 'compact')) {
            return \false;
        }
        $variableName = $variable->name;
        if (!\is_string($variableName)) {
            return \false;
        }
        return $this->isInArgOrArrayItemNodes($funcCall->args, $variableName);
    }
    /**
     * @param array<int, Arg|VariadicPlaceholder|ArrayItem|null> $nodes
     */
    private function isInArgOrArrayItemNodes(array $nodes, string $variableName) : bool
    {
        foreach ($nodes as $node) {
            if ($this->shouldSkip($node)) {
                continue;
            }
            /** @var Arg|ArrayItem $node */
            if ($node->value instanceof \PhpParser\Node\Expr\Array_) {
                if ($this->isInArgOrArrayItemNodes($node->value->items, $variableName)) {
                    return \true;
                }
                continue;
            }
            if (!$node->value instanceof \PhpParser\Node\Scalar\String_) {
                continue;
            }
            if ($node->value->value === $variableName) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Arg|\PhpParser\Node\Expr\ArrayItem|\PhpParser\Node\VariadicPlaceholder|null $node
     */
    private function shouldSkip($node) : bool
    {
        if ($node === null) {
            return \true;
        }
        return $node instanceof \PhpParser\Node\VariadicPlaceholder;
    }
}
