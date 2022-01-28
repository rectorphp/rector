<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

class SkipPassDynamicArg4
{
    private Logger $logger;

    public function __construct(
        ?Logger $logger = null,
    ) {
        $x = 'b';
        $this->logger = $logger ?? new NullLogger([$x => 'a']);
    }
}
