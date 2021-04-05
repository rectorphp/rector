<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject;

final class PhpDocAttributeKey
{
    /**
     * @var string
     */
    public const START_AND_END = 'start_and_end';

    /**
     * @var string
     */
    public const PARENT = 'parent';

    /**
     * @var string
     */
    public const LAST_TOKEN_POSITION = 'last_token_position';
}
