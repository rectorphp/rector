<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Node\Value;

use PhpParser\Node\Expr\Ternary;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class TernaryBracketWrapper
{
    public function wrapWithBracket(\PhpParser\Node\Expr\Ternary $ternary) : void
    {
        $ternary->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND, 'wrapped_with_brackets');
        $ternary->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
    }
}
