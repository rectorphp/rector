<?php

declare (strict_types=1);
namespace Rector\Php74\Tokenizer;

use PhpParser\Node\Expr\Ternary;
use Rector\Core\ValueObject\Application\File;
final class ParenthesizedNestedTernaryAnalyzer
{
    public function isParenthesized(File $file, Ternary $ternary) : bool
    {
        $oldTokens = $file->getOldTokens();
        $startTokenPos = $ternary->getStartTokenPos();
        $endTokenPos = $ternary->getEndTokenPos();
        $hasOpenParentheses = isset($oldTokens[$startTokenPos]) && $oldTokens[$startTokenPos] === '(';
        $hasCloseParentheses = isset($oldTokens[$endTokenPos]) && $oldTokens[$endTokenPos] === ')';
        if ($hasOpenParentheses || $hasCloseParentheses) {
            return \true;
        }
        $hasOpenParentheses = isset($oldTokens[$startTokenPos - 1]) && $oldTokens[$startTokenPos - 1] === '(';
        $hasCloseParentheses = isset($oldTokens[$endTokenPos + 1]) && $oldTokens[$endTokenPos + 1] === ')';
        return $hasOpenParentheses || $hasCloseParentheses;
    }
}
