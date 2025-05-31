<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Tokenizer;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use Rector\ValueObject\Application\File;
final class TrailingCommaRemover
{
    public function remove(File $file, Node $node) : void
    {
        $tokens = $file->getOldTokens();
        $iteration = 1;
        while (isset($tokens[$node->getEndTokenPos() + $iteration])) {
            if (\trim($tokens[$node->getEndTokenPos() + $iteration]->text) === '') {
                ++$iteration;
                continue;
            }
            if (\trim($tokens[$node->getEndTokenPos() + $iteration]->text) !== ',') {
                break;
            }
            $tokens[$node->getEndTokenPos() + $iteration]->text = '';
            break;
        }
    }
    public function removeFromCallLike(File $file, CallLike $callLike) : bool
    {
        $tokens = $file->getOldTokens();
        $iteration = 1;
        $hasChanged = \false;
        while (isset($tokens[$callLike->getEndTokenPos() - $iteration])) {
            $text = \trim($tokens[$callLike->getEndTokenPos() - $iteration]->text);
            if (\in_array($text, [')', ''], \true)) {
                ++$iteration;
                continue;
            }
            if ($text === ',') {
                $tokens[$callLike->getEndTokenPos() - $iteration]->text = '';
                $hasChanged = \true;
            }
            break;
        }
        return $hasChanged;
    }
}
