<?php

namespace Rector\PHPUnit\Tests\Rector\ArrayToYieldDataProviderRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MyTestCsae extends TestCase
{
    /**
     * @return mixed[]
     */
    public function provideData(): array
    {
        return [
            ['item']
        ];
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\ArrayToYieldDataProviderRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MyTestCsae extends TestCase
{
    public function provideData(): \Iterator
    {
        yield ['item'];
    }
}

?>
