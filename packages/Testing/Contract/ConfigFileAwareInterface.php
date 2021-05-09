<?php

declare (strict_types=1);
namespace Rector\Testing\Contract;

interface ConfigFileAwareInterface
{
    public function provideConfigFilePath() : string;
}
