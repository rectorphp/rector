<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class DemoFile
{
    public function run()
    {
         $classInstance->{$methodName}(...$this->getParameters());
    }
    
    private function getParameters(): array{}
}
?>
