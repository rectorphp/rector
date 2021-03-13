<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class ReturnDocblockExists {
    /**
     * This property is the best one
     */
    public function getAnything(): mixed
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

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class ReturnDocblockExists {
    /**
     * This property is the best one
     * @return mixed
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
