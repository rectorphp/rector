<?php

declare(strict_types=1);

namespace Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Source;

use stdClass;

class Service
{
    public static function __callStatic($name, $arguments)
    {
        return Other::$name($arguments);
    }
}

class Other
{
    public static function getAlbum()
    {
        return new stdClass;
    }
}