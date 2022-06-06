<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Composer\Contract;

interface VersionAwareInterface
{
    public function getVersion() : string;
}
