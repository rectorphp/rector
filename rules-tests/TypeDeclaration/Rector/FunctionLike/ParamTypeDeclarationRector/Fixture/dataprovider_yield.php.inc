<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

/**
 * Typehints are inferred from the types of the yielded arrays
 * This can be compared with dataprovider_array.php.inc which has different behavior.
 * See: https://phpunit.readthedocs.io/en/9.3/writing-tests-for-phpunit.html#data-providers
 */
final class DemoYieldStringTest
{
    public function provideThings(): iterable {
        yield [ 123, true, 'I am a string', null, 'a' ];
        yield [ 123, true, 'I am a string', 999, [] ];
        yield [ 123, true, 'I am a string', 999, 123 ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( $one, $two, $three, $four, $five ): void {}
}
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

/**
 * Typehints are inferred from the types of the yielded arrays
 * This can be compared with dataprovider_array.php.inc which has different behavior.
 * See: https://phpunit.readthedocs.io/en/9.3/writing-tests-for-phpunit.html#data-providers
 */
final class DemoYieldStringTest
{
    public function provideThings(): iterable {
        yield [ 123, true, 'I am a string', null, 'a' ];
        yield [ 123, true, 'I am a string', 999, [] ];
        yield [ 123, true, 'I am a string', 999, 123 ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( int $one, bool $two, string $three, ?int $four, $five ): void {}
}
