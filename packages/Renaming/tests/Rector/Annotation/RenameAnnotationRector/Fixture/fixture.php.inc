<?php

namespace Rector\Renaming\Tests\Rector\Annotation\RenameAnnotationRector\Fixture;

final class MyRenameClassTest extends \PHPUnit\Framework\TestCase
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

namespace Rector\Renaming\Tests\Rector\Annotation\RenameAnnotationRector\Fixture;

final class MyRenameClassTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function test()
    {
    }
}

?>
