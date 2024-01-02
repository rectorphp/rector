<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Tokenizer;

use PhpParser\Node;
use Rector\ValueObject\Application\File;
final class FollowedByNewlineOnlyMaybeWithSemicolonAnalyzer
{
    public function isFollowed(File $file, Node $node) : bool
    {
        $oldTokens = $file->getOldTokens();
        $nextTokenPosition = $node->getEndTokenPos() + 1;
        if (isset($oldTokens[$nextTokenPosition]) && $oldTokens[$nextTokenPosition] === ';') {
            ++$nextTokenPosition;
        }
        return !isset($oldTokens[$nextTokenPosition]) || isset($oldTokens[$nextTokenPosition][1]) && \strncmp($oldTokens[$nextTokenPosition][1], "\n", \strlen("\n")) === 0;
    }
}
