<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Renaming\Contract;

use RectorPrefix20220606\PHPStan\Type\ObjectType;
interface MethodCallRenameInterface
{
    public function getClass() : string;
    public function getObjectType() : ObjectType;
    public function getOldMethod() : string;
    public function getNewMethod() : string;
}
