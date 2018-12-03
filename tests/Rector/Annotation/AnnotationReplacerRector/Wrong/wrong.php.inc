<?php

namespace Rector\Tests\Rector\Annotation\AnnotationReplacerRector\Wrong;

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

namespace Rector\Tests\Rector\Annotation\AnnotationReplacerRector\Wrong;

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
