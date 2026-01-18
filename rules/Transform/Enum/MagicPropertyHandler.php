<?php

declare (strict_types=1);
namespace Rector\Transform\Enum;

final class MagicPropertyHandler
{
    /**
     * @var string
     */
    public const GET = 'get';
    /**
     * @var string
     */
    public const SET = 'set';
    /**
     * @var string
     */
    public const ISSET_ = 'exists';
    /**
     * @var string
     */
    public const UNSET = 'unset';
}
