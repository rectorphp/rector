<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Tokenizer;

use PhpParser\Node;
use Rector\Core\Util\StringUtils;
use Rector\Core\ValueObject\Application\File;
final class FollowedByCommaAnalyzer
{
    public function isFollowed(\Rector\Core\ValueObject\Application\File $file, \PhpParser\Node $node) : bool
    {
        $oldTokens = $file->getOldTokens();
        $nextTokenPosition = $node->getEndTokenPos() + 1;
        while (isset($oldTokens[$nextTokenPosition])) {
            $currentToken = $oldTokens[$nextTokenPosition];
            // only space
            if (\is_array($currentToken) || \Rector\Core\Util\StringUtils::isMatch($currentToken, '#\\s+#')) {
                ++$nextTokenPosition;
                continue;
            }
            // without comma
            if (\in_array($currentToken, ['(', ')', ';'], \true)) {
                return \false;
            }
            break;
        }
        return \true;
    }
}
