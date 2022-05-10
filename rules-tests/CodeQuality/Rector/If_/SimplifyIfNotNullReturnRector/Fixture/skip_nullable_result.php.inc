<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector\Fixture;

final class SkipNullableResult
{
    public function run()
    {
        $newNode = $this->resolveMaybe();
        if ($newNode !== null) {
            return null;
        }

        return 5;
    }

    private function resolveMaybe(): ?int
    {
        if (mt_rand(0, 1)) {
            return 100;
        }

        return null;
    }
}
