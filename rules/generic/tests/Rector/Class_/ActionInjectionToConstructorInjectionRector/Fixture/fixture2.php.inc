<?php

namespace Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;
use Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;
use Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\SomeRequest;

final class AnotherController
{
    public function default(ProductRepository $productRepository)
    {
        $products = $productRepository->fetchAll();
    }
    public function detail(SomeRequest $request, SomeProduct $product)
    {
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;
use Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;
use Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\SomeRequest;

final class AnotherController
{
    public function __construct(private \Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\ProductRepository $productRepository)
    {
    }
    public function default()
    {
        $products = $this->productRepository->fetchAll();
    }
    public function detail(SomeRequest $request, SomeProduct $product)
    {
    }
}

?>
