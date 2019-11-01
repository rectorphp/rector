<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

use PhpParser\Node;

final class KeepNestedDimFetchUsed
{
    /**
     * @var string[]
     */
    private $previousToNewFunctions = [
        'reset' => 1,
        'end' =>  2,
    ];

    public function refactor(): ?Node
    {
        $value = $this->previousToNewFunctions['reset'];

        return $value;
    }
}
