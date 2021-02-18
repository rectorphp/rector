<?php

namespace Rector\Core\Tests\Issues\Issue5612\DoNotSetBadParamType\Fixture\Rule;

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
