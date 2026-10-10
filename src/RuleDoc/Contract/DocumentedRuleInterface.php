<?php

declare (strict_types=1);
namespace Rector\RuleDoc\Contract;

use Rector\RuleDoc\RuleDefinition;
/**
 * @api
 */
interface DocumentedRuleInterface
{
    public function getRuleDefinition(): RuleDefinition;
}
