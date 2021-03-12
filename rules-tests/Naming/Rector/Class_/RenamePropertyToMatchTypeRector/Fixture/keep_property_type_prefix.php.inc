<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

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
