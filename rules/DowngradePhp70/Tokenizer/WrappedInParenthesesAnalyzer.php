<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Tokenizer;

use PhpParser\Node;
use Rector\Core\ValueObject\Application\File;
final class WrappedInParenthesesAnalyzer
{
    public function isParenthesized(\Rector\Core\ValueObject\Application\File $file, \PhpParser\Node $node) : bool
    {
        $oldTokens = $file->getOldTokens();
        $startTokenPos = $node->getStartTokenPos();
        $endTokenPos = $node->getEndTokenPos();
        $previousTokenPos = $startTokenPos >= 0 ? $startTokenPos - 1 : -1;
        $nextTokenPos = $endTokenPos >= 0 ? $endTokenPos + 1 : -1;
        return isset($oldTokens[$previousTokenPos]) && $oldTokens[$previousTokenPos] === '(' && isset($oldTokens[$nextTokenPos]) && $oldTokens[$nextTokenPos] === ')';
    }
}
