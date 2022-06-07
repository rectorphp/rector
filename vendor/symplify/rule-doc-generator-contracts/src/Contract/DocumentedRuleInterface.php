<?php

declare (strict_types=1);
namespace RectorPrefix20220607\Symplify\RuleDocGenerator\Contract;

use RectorPrefix20220607\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @api
 */
interface DocumentedRuleInterface
{
    public function getRuleDefinition() : RuleDefinition;
}
