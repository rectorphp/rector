<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Arguments\Contract;

interface ReplaceArgumentDefaultValueInterface
{
    public function getPosition() : int;
    /**
     * @return mixed
     */
    public function getValueBefore();
    /**
     * @return mixed
     */
    public function getValueAfter();
}
