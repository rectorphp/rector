<?php

declare (strict_types=1);
namespace Rector\Arguments\Contract;

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
