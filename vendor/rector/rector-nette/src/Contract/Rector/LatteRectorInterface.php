<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\Contract\Rector;

use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
interface LatteRectorInterface extends RectorInterface
{
    public function changeContent(string $content) : string;
}
