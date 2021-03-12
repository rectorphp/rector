<?php

namespace Rector\Tests\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector\Fixture;

use Rector\Tests\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;

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

namespace Rector\Tests\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector\Fixture;

use Rector\Tests\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;

final class SomeController
{
    public function __construct(private \Rector\Tests\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\ProductRepository $productRepository)
    {
    }
    public function default()
    {
        $products = $this->productRepository->fetchAll();
    }
}

?>
