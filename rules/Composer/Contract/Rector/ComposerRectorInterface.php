<?php

declare (strict_types=1);
namespace Rector\Composer\Contract\Rector;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix202208\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
interface ComposerRectorInterface extends RectorInterface, ConfigurableRectorInterface
{
    public function refactor(ComposerJson $composerJson) : void;
}
