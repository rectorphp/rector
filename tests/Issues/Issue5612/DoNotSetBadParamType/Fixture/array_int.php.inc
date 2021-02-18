<?php

namespace Rector\Core\Tests\Issues\Issue5612\DoNotSetBadParamType\Fixture;

final class DemoArrayIntTest
{
    public function provideThings(): array {
        return [
            [ 1234 ],
        ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( $id ): void {}
}
-----
<?php

namespace Rector\Core\Tests\Issues\Issue5612\DoNotSetBadParamType\Fixture;

final class DemoArrayIntTest
{
    /**
     * @return int[][]
     */
    public function provideThings(): array {
        return [
            [ 1234 ],
        ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( $id ): void {}
}
