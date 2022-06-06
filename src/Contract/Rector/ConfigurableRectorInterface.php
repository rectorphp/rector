<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Contract\Rector;

use RectorPrefix20220606\Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
interface ConfigurableRectorInterface extends ConfigurableRuleInterface
{
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void;
}
