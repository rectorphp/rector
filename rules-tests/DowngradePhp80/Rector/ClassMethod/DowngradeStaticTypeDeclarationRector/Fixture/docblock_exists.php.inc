<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector\Fixture;

class DocblockExists {
    /**
     * This property is the best one
     */
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

class DocblockExists {
    /**
     * This property is the best one
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
