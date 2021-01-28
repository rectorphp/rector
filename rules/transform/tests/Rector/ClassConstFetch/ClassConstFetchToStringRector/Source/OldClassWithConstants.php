<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\ClassConstFetch\ClassConstFetchToStringRector\Source;

final class OldClassWithConstants
{
    /**
     * @var string
     */
    public const DEVELOPMENT =  'development';

    /**
     * @var string
     */
    public const PRODUCTION =  'production';
}
