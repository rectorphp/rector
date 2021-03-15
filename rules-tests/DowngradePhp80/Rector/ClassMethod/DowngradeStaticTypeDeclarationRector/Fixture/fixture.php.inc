<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector\Fixture;

class Fixture
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

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector\Fixture;

class Fixture
{
    /**
     * @return $this
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
