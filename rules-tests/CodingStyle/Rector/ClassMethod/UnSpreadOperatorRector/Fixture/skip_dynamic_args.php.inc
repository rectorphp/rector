<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class SkipDynamicArgs
{
    public function run()
    {
         $classInstance->{$methodName}(...$this->getParameters());
    }

    private function getParameters(): array{}
}
?>
