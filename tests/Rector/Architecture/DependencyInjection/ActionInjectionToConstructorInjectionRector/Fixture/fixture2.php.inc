<?php

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Fixture;

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;
use Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;
use Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeRequest;

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

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Fixture;

use Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;
use Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;
use Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeRequest;

final class AnotherController
{
    private \Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\ProductRepository $productRepository;
    public function __construct(\Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
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
