<?php

declare(strict_types=1);

namespace Rector\Composer\Contract\Rector;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\CoreRectorInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

interface ComposerRectorInterface extends CoreRectorInterface, ConfigurableRectorInterface
{
    public function refactor(ComposerJson $composerJson): void;
}
