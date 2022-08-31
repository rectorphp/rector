<?php

declare (strict_types=1);
namespace Rector\Skipper\Enum;

final class AsteriskMatch
{
    /**
     * @var string
     * @see https://regex101.com/r/aVUDjM/2
     */
    public const ONLY_STARTS_WITH_ASTERISK_REGEX = '#^\\*(.*?)[^*]$#';
    /**
     * @var string
     * @see https://regex101.com/r/ZB2dFV/2
     */
    public const ONLY_ENDS_WITH_ASTERISK_REGEX = '#^[^*](.*?)\\*$#';
}
