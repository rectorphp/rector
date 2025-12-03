<?php

declare (strict_types=1);
namespace Rector\Transform\Enum;

final class MagicPropertyHandler
{
    public const GET = 'get';
    public const SET = 'set';
    public const ISSET_ = 'exists';
    public const UNSET = 'unset';
}
