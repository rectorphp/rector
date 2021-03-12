<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

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
