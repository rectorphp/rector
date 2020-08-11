<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use PHPUnit\Framework\TestCase;

class DataProvider extends TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function testSomething(array $names)
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
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use PHPUnit\Framework\TestCase;

class DataProvider extends TestCase
{
    /**
     * @dataProvider provideData()
     * @param string[] $names
     */
    public function testSomething(array $names)
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
