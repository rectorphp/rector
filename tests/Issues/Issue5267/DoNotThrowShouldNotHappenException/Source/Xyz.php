<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue5267\DoNotThrowShouldNotHappenException\Source;

class Xyz
{
    /**
     * @var Xyz
     */
    public $abc;

    public function get(string $s): string
    {
        return $s;
    }
}
