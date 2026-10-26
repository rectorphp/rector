<?php

declare (strict_types=1);
namespace Rector\Contract\Rector;

interface OrderedRectorInterface
{
    /**
     * Rule that must run before this one, so dependent changes converge in a single file pass.
     *
     * @return class-string<RectorInterface>
     */
    public function precededRule(): string;
}
