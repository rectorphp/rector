<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

/**
 * Test case including a dataProvider using yield that is skipped and no typehint added to $id.
 * This can be compared with dataprovider_array.php.inc which has different behavior.
 * See: https://phpunit.readthedocs.io/en/9.3/writing-tests-for-phpunit.html#data-providers
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
