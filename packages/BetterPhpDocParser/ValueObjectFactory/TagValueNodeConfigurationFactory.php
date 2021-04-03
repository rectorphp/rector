<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObjectFactory;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\ValueObject\TagValueNodeConfiguration;

final class TagValueNodeConfigurationFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/y3U6s4/1
     */
    public const NEWLINE_AFTER_OPENING_REGEX = '#^(\(\s+|\n)#m';

    /**
     * @var string
     * @see https://regex101.com/r/bopnKI/1
     */
    public const NEWLINE_BEFORE_CLOSING_REGEX = '#(\s+\)|\n(\s+)?)$#m';

    public function createFromOriginalContent(?string $originalContent): TagValueNodeConfiguration
    {
        if ($originalContent === null) {
            return new TagValueNodeConfiguration();
        }

        $hasNewlineAfterOpening = (bool) Strings::match($originalContent, self::NEWLINE_AFTER_OPENING_REGEX);
        $hasNewlineBeforeClosing = (bool) Strings::match($originalContent, self::NEWLINE_BEFORE_CLOSING_REGEX);

        return new TagValueNodeConfiguration($hasNewlineAfterOpening, $hasNewlineBeforeClosing);
    }
}
