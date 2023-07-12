<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector\Fixture;

final class SkipSetMethods extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $applicationMock = $this->getMockBuilder('SomeClass')
            ->disableOriginalConstructor()
            ->setMethods(['addViolation'])
            ->getMock();
    }
}

?>
