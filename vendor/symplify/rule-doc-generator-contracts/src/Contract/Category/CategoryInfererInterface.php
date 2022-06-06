<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Symplify\RuleDocGenerator\Contract\Category;

use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
interface CategoryInfererInterface
{
    public function infer(RuleDefinition $ruleDefinition) : ?string;
}
