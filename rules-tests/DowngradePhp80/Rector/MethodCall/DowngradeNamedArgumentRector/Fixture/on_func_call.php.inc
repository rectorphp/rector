<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class OnFuncCall
{
    public function run()
    {
        json_decode('{}', associative: true, flags: JSON_THROW_ON_ERROR);
        json_decode('{}', flags: JSON_THROW_ON_ERROR);
        json_decode(associative: true, flags: JSON_THROW_ON_ERROR, json: '{}');

        json_encode([], depth: 10);
        json_encode([], flags: JSON_FORCE_OBJECT, depth: 100);

        class_exists(class: 'stdClass');
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class OnFuncCall
{
    public function run()
    {
        json_decode('{}', true, 512, JSON_THROW_ON_ERROR);
        json_decode('{}', null, 512, JSON_THROW_ON_ERROR);
        json_decode('{}', true, 512, JSON_THROW_ON_ERROR);

        json_encode([], 0, 10);
        json_encode([], JSON_FORCE_OBJECT, 100);

        class_exists('stdClass');
    }
}

?>
