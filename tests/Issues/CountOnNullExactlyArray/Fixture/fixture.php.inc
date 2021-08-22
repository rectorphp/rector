<?php

namespace Rector\Core\Tests\Issues\CountOnNullExactlyArray\Fixture;

final class Fixture
{
    public function run()
    {
        $array = [];
        if (count($array) > 0) {
        }
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\CountOnNullExactlyArray\Fixture;

final class Fixture
{
    /**
     * @var mixed[]
     */
    private const ARRAY = [];
    public function run()
    {
        if (count(self::ARRAY) > 0) {
        }
    }
}

?>
