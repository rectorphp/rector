<?php

declare (strict_types=1);
namespace Symplify\RuleDocGenerator\Contract\Category;

use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
interface CategoryInfererInterface
{
    public function infer(\Symplify\RuleDocGenerator\ValueObject\RuleDefinition $ruleDefinition) : ?string;
}
