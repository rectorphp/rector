<?php

namespace Rector\Architecture\Tests\Rector\Class_\ConstructorInjectionToActionInjectionRector\Fixture;

final class SomeController
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function default()
    {
        $products = $this->productRepository->fetchAll();
    }
}

?>
-----
<?php

namespace Rector\Architecture\Tests\Rector\Class_\ConstructorInjectionToActionInjectionRector\Fixture;

final class SomeController
{
    public function default(ProductRepository $productRepository)
    {
        $products = $productRepository->fetchAll();
    }
}

?>
