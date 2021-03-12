<?php

namespace Rector\Tests\Privatization\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class SkipApi
{
    /**
     * @api
     * @var string
     */
    public const SHORT_NAME = 'use_me_anywhere';
}
