<?php

namespace Rector\Architecture\Tests\Rector\Class_\ConstructorInjectionToActionInjectionRector\Fixture;

use Rector\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;

final class ManageDifferentNamingController
{
    /**
     * @var ProductRepository
     */
    private $repository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->repository = $productRepository;
    }

    public function index()
    {
        return $this->repository->get(5);
    }
}

?>
-----
<?php

namespace Rector\Architecture\Tests\Rector\Class_\ConstructorInjectionToActionInjectionRector\Fixture;

use Rector\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;

final class ManageDifferentNamingController
{
    public function index(ProductRepository $productRepository)
    {
        return $productRepository->get(5);
    }
}

?>
