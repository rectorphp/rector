<?php

declare (strict_types=1);
namespace Rector\Composer\Contract;

interface VersionAwareInterface
{
    public function getVersion() : string;
}
