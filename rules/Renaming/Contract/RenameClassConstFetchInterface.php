<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Renaming\Contract;

use RectorPrefix20220606\PHPStan\Type\ObjectType;
interface RenameClassConstFetchInterface
{
    public function getOldObjectType() : ObjectType;
    public function getOldConstant() : string;
    public function getNewConstant() : string;
}
