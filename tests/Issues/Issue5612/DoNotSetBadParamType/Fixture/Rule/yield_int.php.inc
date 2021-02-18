<?php

namespace Rector\Core\Tests\Issues\Issue5612\DoNotSetBadParamType\Fixture\Rule;

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
