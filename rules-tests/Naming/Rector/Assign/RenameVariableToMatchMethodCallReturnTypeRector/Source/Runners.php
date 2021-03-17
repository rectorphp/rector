<?php

declare(strict_types=1);

namespace Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source;

final class Runners
{
    /**
     * @return FastRunner
     */
    public function getFast()
    {
        return new FastRunner();
    }
}
