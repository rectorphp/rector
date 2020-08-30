<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source;

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
