<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeReturnMixedTypeDeclarationRector\Fixture;

class DocblockTagExists {
    /**
     * This property is the best one
     * @return mixed
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

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeReturnMixedTypeDeclarationRector\Fixture;

class DocblockTagExists {
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
