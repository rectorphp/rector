<?php

declare (strict_types=1);
namespace Rector\RuleDoc\Contract\Category;

use Rector\RuleDoc\RuleDefinition;
/**
 * @api
 */
interface CategoryInfererInterface
{
    public function infer(RuleDefinition $ruleDefinition): ?string;
}
