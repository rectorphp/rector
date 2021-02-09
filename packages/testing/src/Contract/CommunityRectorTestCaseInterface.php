<?php

declare(strict_types=1);

namespace Rector\Testing\Contract;

interface CommunityRectorTestCaseInterface
{
    public function provideConfigFilePath(): string;
}
