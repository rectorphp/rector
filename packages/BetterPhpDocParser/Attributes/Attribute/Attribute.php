<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Attribute;

use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;

final class Attribute
{
    /**
     * @var string
     */
    public const HAS_DESCRIPTION_WITH_ORIGINAL_SPACES = 'has_description_with_restored_spaces';

    /**
     * @experiment
     * @var string
     */
    public const START_END = StartAndEnd::class;

    /**
     * @var string
     */
    public const LAST_TOKEN_POSITION = 'last_token_position';

    /**
     * @var string
     */
    public const ORIGINAL_CONTENT = 'original_content';
}
