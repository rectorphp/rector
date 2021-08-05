<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\FixturePhp74;

final class SkipReturnStatic
{
    public function run()
    {
        return new static();
    }
}
?>
