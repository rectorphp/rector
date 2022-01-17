<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class SkipOnIterable
{
    private function findAll(): iterable
    {
        return [];
    }

    public function run()
    {
        return count($this->findAll());
    }
}
