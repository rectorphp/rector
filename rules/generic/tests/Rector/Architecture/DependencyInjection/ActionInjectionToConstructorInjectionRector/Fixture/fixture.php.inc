<?php

namespace Rector\Generic\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Fixture;

use Rector\Generic\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;

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

namespace Rector\Generic\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Fixture;

use Rector\Generic\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;

final class SomeController
{
    private \Rector\Generic\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\ProductRepository $productRepository;
    public function __construct(\Rector\Generic\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    public function default()
    {
        $products = $this->productRepository->fetchAll();
    }
}

?>
