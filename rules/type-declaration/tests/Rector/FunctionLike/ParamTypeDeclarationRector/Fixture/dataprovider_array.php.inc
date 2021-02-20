<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

/**
 * Test case including a dataProvider using an array that lead to an incorrect types being added.
 * `array` was added for $one, but should be `string`, or the parameter should be left alone entirely.
 * The $two and $three parameters did not get hints added at all.
 * See: https://phpunit.readthedocs.io/en/9.3/writing-tests-for-phpunit.html#data-providers
 */
final class DemoArrayTest
{
    public function provideThings(): array {
        return [
            [ 123, true, 'I am a string', null ],
            [ 123, true, 'I am a string', 999 ],
        ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( $one, $two, $three, $four ): void {}
}
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

/**
 * Test case including a dataProvider using an array that lead to an incorrect types being added.
 * `array` was added for $one, but should be `string`, or the parameter should be left alone entirely.
 * The $two and $three parameters did not get hints added at all.
 * See: https://phpunit.readthedocs.io/en/9.3/writing-tests-for-phpunit.html#data-providers
 */
final class DemoArrayTest
{
    public function provideThings(): array {
        return [
            [ 123, true, 'I am a string', null ],
            [ 123, true, 'I am a string', 999 ],
        ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( int $one, bool $two, string $three, $four ): void {}
}
