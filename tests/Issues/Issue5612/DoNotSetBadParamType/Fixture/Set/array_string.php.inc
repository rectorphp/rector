<?php

namespace Rector\Core\Tests\Issues\Issue5612\DoNotSetBadParamType\Fixture\Set;

final class DemoArrayStringTest
{
    public function provideThings(): array {
        return [
            [ 'I am a string' ],
        ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( $id ): void {}
}
-----
<?php

namespace Rector\Core\Tests\Issues\Issue5612\DoNotSetBadParamType\Fixture\Set;

final class DemoArrayStringTest
{
    /**
     * @return string[][]
     */
    public function provideThings(): array {
        return [
            [ 'I am a string' ],
        ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( $id ): void {}
}
