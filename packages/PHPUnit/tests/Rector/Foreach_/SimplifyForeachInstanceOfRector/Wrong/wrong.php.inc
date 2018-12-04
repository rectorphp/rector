<?php

final class MyOtherTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $foos = [];
        foreach ($foos as $foo) {
            $this->assertInstanceOf(\SplFileInfo::class, $foo);
        }

        foreach ($foos as $foo) {
            self::assertInstanceOf(\SplFileInfo::class, $foo);
        }

        foreach ($foos as $bar => $foo) {
            $this->assertInstanceOf(\SplFileInfo::class, $bar);
        }

        foreach ($foos as $foo) {
            $this->assertInstanceOf(\SplFileInfo::class, $foos);
        }
    }
}

?>
-----
<?php

final class MyOtherTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $foos = [];
        $this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);

        self::assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);

        foreach ($foos as $bar => $foo) {
            $this->assertInstanceOf(\SplFileInfo::class, $bar);
        }

        foreach ($foos as $foo) {
            $this->assertInstanceOf(\SplFileInfo::class, $foos);
        }
    }
}

?>
