<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertNotSame($anything, $something);
        $this->assertGreaterThan(2, count($something));
        $this->assertLessThanOrEqual(5, count($something), 'message');
    }
}