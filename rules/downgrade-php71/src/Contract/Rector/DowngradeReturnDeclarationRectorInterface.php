<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Contract\Rector;

use PhpParser\Node\FunctionLike;

interface DowngradeReturnDeclarationRectorInterface
{
    /**
     * Indicate if the return declaration must be removed
     */
    public function shouldRemoveReturnDeclaration(FunctionLike $functionLike): bool;
}
