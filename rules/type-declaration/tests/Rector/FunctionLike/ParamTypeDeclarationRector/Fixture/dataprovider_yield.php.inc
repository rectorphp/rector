<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

/**
 * Test case including a dataProvider using yield that is skipped and no typehint added to $id.
 * This can be compared with dataprovider_array.php.inc which has different behavior.
 */
final class DemoYieldStringTest
{
    public function provideThings(): iterable {
        yield [ 'I am a string' ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( $one ): void {}
}
