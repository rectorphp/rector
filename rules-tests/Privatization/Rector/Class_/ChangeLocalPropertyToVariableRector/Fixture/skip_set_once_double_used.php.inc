<?php

declare(strict_types=1);

namespace Rector\Tests\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\String_;

final class SkipSetOnceDoubleUsed
{
    /**
     * @var bool
     */
    private $nodeReplacementIsRestricted = false;

    public function refactor(Node $node): ?Node
    {
        $this->nodeReplacementIsRestricted = false;

        $joinedNode = $this->joinConcatIfStrings($node);
        if ($this->nodeReplacementIsRestricted) {
            return null;
        }
    }

    /**
     * @return Concat|String_
     */
    private function joinConcatIfStrings(Concat $node): Expr
    {
        if ($node) {
            $this->nodeReplacementIsRestricted = true;
            return $node;
        }
    }
}
