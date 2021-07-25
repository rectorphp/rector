<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class ObjectCastToStdClass
{
    /**
     * @var object[]
     */
    private static $services = [];

    public static function register(array $service)
    {
        self::$services[] = (object) $service;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class ObjectCastToStdClass
{
    /**
     * @var \stdClass[]
     */
    private static $services = [];

    public static function register(array $service)
    {
        self::$services[] = (object) $service;
    }
}

?>
