<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Rector;

use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
interface ConfigurableRectorInterface extends \Rector\Core\Contract\Rector\RectorInterface, ConfigurableRuleInterface
{
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void;
}
