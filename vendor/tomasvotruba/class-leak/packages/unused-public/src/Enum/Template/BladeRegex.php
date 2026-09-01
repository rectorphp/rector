<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\UnusedPublic\Enum\Template;

final class BladeRegex
{
    /**
     * @see https://regex101.com/r/vDKvtE/1
     * @var string
     */
    public const INNER_REGEX = '#\{(\{|\!\!)(?<contents>.*?)(\!\!|\})\}#';
    /**
     * @see https://regex101.com/r/3nbDDK/1
     * @var string
     */
    public const TAG_REGEX = '#@\w+(?<contents>.*?)\n#';
    /**
     * @see https://regex101.com/r/P1EaIR/1
     * @var string
     */
    public const METHOD_CALL_REGEX = '#\w+(\-\>|::)(?<desired_name>\w+)\((.*?)\)#';
    /**
     * Matches a property fetch "$var->name" that is not a method call, e.g. {{ $value->name }}
     * @var string
     */
    public const PROPERTY_FETCH_REGEX = '#\-\>(?<desired_name>\w+)(?!\s*\()#';
    /**
     * @see https://regex101.com/r/pBkm53/1
     * @var string
     */
    public const CONSTANT_FETCH_REGEX = '#\w+::(?<desired_name>[\w_]+)#';
}
