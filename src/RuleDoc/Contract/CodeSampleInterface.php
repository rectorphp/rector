<?php

declare (strict_types=1);
namespace Rector\RuleDoc\Contract;

interface CodeSampleInterface
{
    public function getGoodCode(): string;
    public function getBadCode(): string;
}
