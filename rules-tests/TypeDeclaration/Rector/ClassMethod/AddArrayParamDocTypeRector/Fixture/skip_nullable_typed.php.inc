<?php
namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

class SkipNullableTyped
{
    public bool $bar;

    public function setBar(?bool $bar): void
    {
        if ($bar === null) {
            $bar = false;
        }

        $this->bar = $bar;
    }
}
?>
