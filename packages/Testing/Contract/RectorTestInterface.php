<?php

declare (strict_types=1);
namespace Rector\Testing\Contract;

interface RectorTestInterface
{
    public function provideConfigFilePath() : string;
}
