<?php

declare (strict_types=1);
namespace Rector\Naming\Contract;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
interface RenameParamValueObjectInterface extends \Rector\Naming\Contract\RenameValueObjectInterface
{
    public function getFunctionLike() : FunctionLike;
    public function getParam() : Param;
}
