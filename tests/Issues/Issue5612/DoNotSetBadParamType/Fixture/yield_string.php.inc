<?php

namespace Rector\Core\Tests\Issues\Issue5612\DoNotSetBadParamType\Fixture;

final class DemoYieldStringTest
{
    public function provideThings(): iterable {
        yield [ 'I am a string' ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( $id ): void {}
}
-----
<?php

namespace Rector\Core\Tests\Issues\Issue5612\DoNotSetBadParamType\Fixture;

final class DemoYieldStringTest
{
    /**
     * @return string[][]
     */
    public function provideThings(): iterable {
        yield [ 'I am a string' ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( $id ): void {}
}
