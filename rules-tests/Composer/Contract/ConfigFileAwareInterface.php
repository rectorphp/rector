<?php

declare(strict_types=1);

namespace Rector\Composer\Tests\Contract;

interface ConfigFileAwareInterface
{
    public function provideConfigFile(): string;
}
