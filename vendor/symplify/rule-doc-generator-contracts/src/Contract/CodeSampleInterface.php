<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Symplify\RuleDocGenerator\Contract;

interface CodeSampleInterface
{
    public function getGoodCode() : string;
    public function getBadCode() : string;
}
