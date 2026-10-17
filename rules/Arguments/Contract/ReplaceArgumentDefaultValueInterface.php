<?php

declare (strict_types=1);
namespace Rector\Arguments\Contract;

interface ReplaceArgumentDefaultValueInterface
{
    /**
     * @var string
     */
    public const ANY_VALUE_BEFORE = '*ANY_VALUE_BEFORE*';
    public function getPosition(): int;
    /**
     * @return mixed
     */
    public function getValueBefore();
    /**
     * @return mixed
     */
    public function getValueAfter();
}
