<?php

namespace Rector\Tests\Rector\Annotation\RenameAnnotationRector\Fixture;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @scenario
     */
    public function test()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Annotation\RenameAnnotationRector\Fixture;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function test()
    {
    }
}

?>
