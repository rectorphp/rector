<?php

namespace Rector\PHPUnit\Tests\Rector\ArrayToYieldDataProviderRector\Wrong;

use PHPUnit\Framework\TestCase;

final class MyTestCase2 extends TestCase
{
    /**
     * @return mixed[]
     */
    public function provideData(): array
    {
        return [
            # some comment
            ['item']
        ];
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\ArrayToYieldDataProviderRector\Wrong;

use PHPUnit\Framework\TestCase;

final class MyTestCase2 extends TestCase
{
    public function provideData(): \Iterator
    {
        # some comment
        yield ['item'];
    }
}

?>
