<?php

declare (strict_types=1);
namespace Rector\Php80\Enum;

use RectorPrefix20220418\MyCLabs\Enum\Enum;
/**
 * @method static MatchKind NORMAL()
 * @method static MatchKind ASSIGN()
 * @method static MatchKind RETURN()
 * @method static MatchKind THROW()
 */
final class MatchKind extends \RectorPrefix20220418\MyCLabs\Enum\Enum
{
    /**
     * @var string
     */
    private const NORMAL = 'normal';
    /**
     * @var string
     */
    private const ASSIGN = 'assign';
    /**
     * @var string
     */
    private const RETURN = 'return';
    /**
     * @var string
     */
    private const THROW = 'throw';
}
