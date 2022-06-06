<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php74\Tokenizer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
final class FollowedByCurlyBracketAnalyzer
{
    public function isFollowed(File $file, Node $node) : bool
    {
        $oldTokens = $file->getOldTokens();
        $endTokenPost = $node->getEndTokenPos();
        if (isset($oldTokens[$endTokenPost]) && $oldTokens[$endTokenPost] === '}') {
            $startTokenPost = $node->getStartTokenPos();
            return !(isset($oldTokens[$startTokenPost][1]) && $oldTokens[$startTokenPost][1] === '${');
        }
        return \false;
    }
}
