<?php

declare(strict_types=1);

namespace E2e\Parallel\Custom\Config;

class SomeClass
{
    public function __construct(
        private readonly \stdClass $stdClass
    )
    {
    }
}
