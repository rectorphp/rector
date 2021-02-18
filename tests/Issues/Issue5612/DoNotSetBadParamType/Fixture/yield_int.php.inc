<?php

namespace Rector\Core\Tests\Issues\Issue5612\DoNotSetBadParamType\Fixture;

final class DemoYieldIntTest
{
    public function provideThings(): iterable {
        yield [ 1234 ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( $id ): void {}
}
-----
<?php

namespace Rector\Core\Tests\Issues\Issue5612\DoNotSetBadParamType\Fixture;

final class DemoYieldIntTest
{
    /**
     * @return int[][]
     */
    public function provideThings(): iterable {
        yield [ 1234 ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( $id ): void {}
}
