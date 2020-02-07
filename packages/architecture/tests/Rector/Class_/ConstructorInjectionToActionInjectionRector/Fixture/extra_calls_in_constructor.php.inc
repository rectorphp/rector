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

    /**
     * @var int
     */
    private $count;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
        $this->default = $productRepository->getDefault();
        $this->count = $this->productRepository->getCount();
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
    /**
     * @var Product
     */
    private $default;
    /**
     * @var int
     */
    private $count;
    public function __construct(ProductRepository $productRepository)
    {
        $this->default = $productRepository->getDefault();
        $this->count = $productRepository->getCount();
    }
    public function default(ProductRepository $productRepository)
    {
        $products = $productRepository->fetchAll();
    }
}

?>
