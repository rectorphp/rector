<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\UnusedPublic\Enum\Template;

final class TwigRegex
{
    /**
     * @see https://regex101.com/r/3gLWCt/1
     * @var string
     */
    public const INNER_REGEX = '#\{(\{|%)(?<contents>.*?)(\}|%)\}#';
    /**
     * @see https://regex101.com/r/G7zAue/1
     * @var string
     */
    public const METHOD_CALL_REGEX = '#\w+\.(?<desired_name>\w+)#';
}
