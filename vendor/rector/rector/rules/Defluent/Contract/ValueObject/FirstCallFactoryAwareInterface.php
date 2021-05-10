<?php

declare (strict_types=1);
namespace Rector\Defluent\Contract\ValueObject;

interface FirstCallFactoryAwareInterface
{
    public function isFirstCallFactory() : bool;
}
