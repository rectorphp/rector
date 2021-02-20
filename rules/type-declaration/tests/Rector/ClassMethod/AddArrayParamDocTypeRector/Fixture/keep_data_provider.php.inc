<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use PHPUnit\Framework\TestCase;

/**
 * Though provideData returns an array. dataProviders take the values 2 levels deep inside this array.
 * This code shouldn't be altered by AddArrayParamDocTypeRector as the doc type should not be an array.
 * It would be a string.
 * See: https://phpunit.readthedocs.io/en/9.3/writing-tests-for-phpunit.html#data-providers
 */
class DataProvider extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSomething( $name1, $name2 )
    {
    }

    public function provideData(): array
    {
        return [
            ['Bob', 'Josh']
        ];
    }
}

?>
