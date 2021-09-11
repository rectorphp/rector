<?php

declare (strict_types=1);
namespace Rector\Php74\Tokenizer;

use PhpParser\Node;
use Rector\Core\ValueObject\Application\File;
final class FollowedByCurlyBracketAnalyzer
{
    public function isFollowed(\Rector\Core\ValueObject\Application\File $file, \PhpParser\Node $node) : bool
    {
        $oldTokens = $file->getOldTokens();
        $endTokenPost = $node->getEndTokenPos();
        return isset($oldTokens[$endTokenPost]) && $oldTokens[$endTokenPost] === '}';
    }
}
