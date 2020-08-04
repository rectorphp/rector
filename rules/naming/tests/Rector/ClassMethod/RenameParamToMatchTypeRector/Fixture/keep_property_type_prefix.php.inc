<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use PhpParser\Node\Expr\MethodCall;

final class KeepPropertyTypePrefix
{
    /**
     * @var MethodCall
     */
    private $getStatusCodeMethodCall;

    private function run()
    {
        return $this->getStatusCodeMethodCall;
    }
}
