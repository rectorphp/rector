<?php

declare(strict_types=1);

namespace Rector\DowngradePhp73\Tokenizer;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Core\Application\TokensByFilePathStorage;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FollowedByCommaAnalyzer
{
    /**
     * @var TokensByFilePathStorage
     */
    private $tokensByFilePathStorage;

    public function __construct(TokensByFilePathStorage $tokensByFilePathStorage)
    {
        $this->tokensByFilePathStorage = $tokensByFilePathStorage;
    }

    public function isFollowed(Node $node): bool
    {
        $smartFileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if (! $smartFileInfo instanceof SmartFileInfo) {
            return false;
        }

        if (! $this->tokensByFilePathStorage->hasForFileInfo($smartFileInfo)) {
            return false;
        }

        $parsedStmtsAndTokens = $this->tokensByFilePathStorage->getForFileInfo($smartFileInfo);
        $oldTokens = $parsedStmtsAndTokens->getOldTokens();

        $nextTokenPosition = $node->getEndTokenPos() + 1;
        while (isset($oldTokens[$nextTokenPosition])) {
            $currentToken = $oldTokens[$nextTokenPosition];

            // only space
            if (is_array($currentToken) || Strings::match($currentToken, '#\s+#')) {
                ++$nextTokenPosition;
                continue;
            }

            // without comma
            if ($currentToken === ')') {
                return false;
            }

            break;
        }

        return true;
    }
}
