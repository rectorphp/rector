<?php

namespace Rector\Architecture\Tests\Rector\Class_\ConstructorInjectionToActionInjectionRector\ExtraCallsInConstructor;

final class SomeController
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Product
     */
    private $default;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
        $this->default = $productRepository->getDefault();
    }

    public function default()
    {
        $products = $this->productRepository->fetchAll();
    }
}

?>
-----
<?php

namespace Rector\Architecture\Tests\Rector\Class_\ConstructorInjectionToActionInjectionRector\ExtraCallsInConstructor;

final class SomeController
{
    public function __construct()
    {
        $this->default = $productRepository->getDefault();
    }
    public function default(ProductRepository $productRepository)
    {
        $products = $productRepository->fetchAll();
    }
}

?>
