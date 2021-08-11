<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\CallWithVoid;

final class SkipArrowFunctionVoid
{
    public function run(CallWithVoid $callWithVoid)
    {
        fn() => $callWithVoid->nothing();
    }
}
