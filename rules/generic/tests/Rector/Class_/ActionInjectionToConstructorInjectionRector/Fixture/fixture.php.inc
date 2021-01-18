<?php

namespace Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;

final class SomeController
{
    public function default(ProductRepository $productRepository)
    {
        $products = $productRepository->fetchAll();
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;

final class SomeController
{
    public function __construct(private \Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\ProductRepository $productRepository)
    {
    }
    public function default()
    {
        $products = $this->productRepository->fetchAll();
    }
}

?>
