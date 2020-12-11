<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Contract\Rector;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;

interface DowngradeParamDeclarationRectorInterface
{
    /**
     * Indicate if the parameter must be removed
     */
    public function shouldRemoveParamDeclaration(Param $param, FunctionLike $functionLike): bool;
}
