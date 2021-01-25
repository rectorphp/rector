<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class SkipDynamicArgs
{
    public function run()
    {
         $classInstance->{$methodName}(...$this->getParameters());
    }

    private function getParameters(): array{}
}
?>
