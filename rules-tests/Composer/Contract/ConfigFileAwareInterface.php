<?php

declare(strict_types=1);

namespace Rector\Tests\Composer\Contract;

interface ConfigFileAwareInterface
{
    public function provideConfigFile(): string;
}
