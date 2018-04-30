<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $response = new \Namespaced\Response();
        $this->assertObjectNotHasAttribute('property', $response);
    }
}
