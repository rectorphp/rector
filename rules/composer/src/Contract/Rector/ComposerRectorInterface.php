<?php

declare(strict_types=1);

namespace Rector\Composer\Contract\Rector;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;

interface ComposerRectorInterface extends RectorInterface, ConfigurableRuleInterface, ConfigurableRectorInterface
{
    public function refactor(ComposerJson $composerJson): void;
}
