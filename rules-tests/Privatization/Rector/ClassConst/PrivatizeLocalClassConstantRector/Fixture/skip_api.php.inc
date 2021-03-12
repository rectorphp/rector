<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class SkipApi
{
    /**
     * @api
     * @var string
     */
    public const SHORT_NAME = 'use_me_anywhere';
}
