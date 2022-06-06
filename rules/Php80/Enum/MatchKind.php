<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php80\Enum;

final class MatchKind
{
    /**
     * @var string
     */
    public const NORMAL = 'normal';
    /**
     * @var string
     */
    public const ASSIGN = 'assign';
    /**
     * @var string
     */
    public const RETURN = 'return';
    /**
     * @var string
     */
    public const THROW = 'throw';
}
