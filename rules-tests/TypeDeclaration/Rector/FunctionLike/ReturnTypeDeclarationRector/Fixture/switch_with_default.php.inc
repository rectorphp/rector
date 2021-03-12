<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class SwitchWithDefault
{
    public function someMethod($value)
    {
        switch ($value) {
            case (1):
                return 'one';
            case (2):
                return 'two';
            default:
                return 'three';
        }
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class SwitchWithDefault
{
    public function someMethod($value): string
    {
        switch ($value) {
            case (1):
                return 'one';
            case (2):
                return 'two';
            default:
                return 'three';
        }
    }
}

?>
