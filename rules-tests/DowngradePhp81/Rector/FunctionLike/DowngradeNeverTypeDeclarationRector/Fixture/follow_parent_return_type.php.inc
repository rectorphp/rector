<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\Fixture;

use Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\Source\RunnableInterface;

class ImplementorRunnable implements RunnableInterface {
    public function run(): never
    {
        throw new \Exception('test');
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\Fixture;

use Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\Source\RunnableInterface;

class ImplementorRunnable implements RunnableInterface {
    /**
     * @return \stdClass|null
     */
    public function run(): ?\stdClass
    {
        throw new \Exception('test');
    }
}

?>
