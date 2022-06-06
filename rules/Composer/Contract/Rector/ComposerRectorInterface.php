<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Composer\Contract\Rector;

use RectorPrefix20220606\Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
interface ComposerRectorInterface extends RectorInterface, ConfigurableRectorInterface
{
    public function refactor(ComposerJson $composerJson) : void;
}
