<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\Contract;

interface RenameValueObjectInterface
{
    public function getCurrentName() : string;
    public function getExpectedName() : string;
}
