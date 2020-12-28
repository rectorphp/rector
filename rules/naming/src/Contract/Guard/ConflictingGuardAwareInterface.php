<?php

declare(strict_types=1);

namespace Rector\Naming\Contract\Guard;

interface ConflictingGuardAwareInterface
{
    public function provideGuard(): ConflictingGuardInterface;
}
