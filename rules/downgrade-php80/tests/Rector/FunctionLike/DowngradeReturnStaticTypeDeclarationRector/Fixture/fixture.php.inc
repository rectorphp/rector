<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeReturnStaticTypeDeclarationRector\Fixture;

class SomeClass
{
    public function getAnything(): static
    {
        if (mt_rand()) {
            return 1;
        }

        return 'value';
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeReturnStaticTypeDeclarationRector\Fixture;

class SomeClass
{
    /**
     * @return static
     */
    public function getAnything()
    {
        if (mt_rand()) {
            return 1;
        }

        return 'value';
    }
}

?>
