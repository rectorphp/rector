<?php

declare(strict_types=1);

namespace Rector\Naming\ValueObject;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;

interface RenameParamValueObjectInterface extends RenameValueObjectInterface
{
    public function getFunctionLike(): FunctionLike;

    public function getParam(): Param;
}
