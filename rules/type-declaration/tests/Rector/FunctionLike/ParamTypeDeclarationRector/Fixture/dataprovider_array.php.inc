<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

/**
 * Test case including a dataProvider using an array that lead to an incorrect types being added.
 * `array` was added for $one, but should be `string`, or the parameter should be left alone entirely.
 * The $two and $three parameters did not get hints added at all.
 */
final class DemoArrayTest
{
    public function provideThings(): array {
        return [
            [ 123, true, 'I am a string' ],
        ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( $one, $two, $three ): void {}
}
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

/**
 * Test case including a dataProvider using an array that lead to an incorrect types being added.
 * `array` was added for $one, but should be `string`, or the parameter should be left alone entirely.
 * The $two and $three parameters did not get hints added at all.
 */
final class DemoArrayTest
{
    public function provideThings(): array {
        return [
            [ 123, true, 'I am a string' ],
        ];
    }

    /**
     * @dataProvider provideThings
     */
    public function testGetFromId( int $one, bool $two, string $three ): void {}
}
