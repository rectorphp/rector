<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Value;

use PhpParser\Node\Expr\Ternary;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class TernaryBracketWrapper
{
    public function wrapWithBracket(Ternary $ternary): void
    {
        $ternary->setAttribute(AttributeKey::KIND, 'wrapped_with_brackets');
        $ternary->setAttribute(AttributeKey::ORIGINAL_NODE, null);
    }
}
