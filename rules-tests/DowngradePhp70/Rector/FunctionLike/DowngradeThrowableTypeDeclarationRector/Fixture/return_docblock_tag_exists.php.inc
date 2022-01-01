<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class ReturnDocblockTagExists {
    /**
     * This property is the best one
     * @return \Throwable
     */
    public function getAnything(): \Throwable
    {
        return new \Exception('Yikes!');
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class ReturnDocblockTagExists {
    /**
     * This property is the best one
     * @return \Throwable
     */
    public function getAnything()
    {
        return new \Exception('Yikes!');
    }
}

?>
