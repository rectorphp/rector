<?php

namespace Rector\Architecture\Tests\Rector\Class_\ConstructorInjectionToActionInjectionRector\Multiple;

use Rector\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;

final class SkipNonActionMethodsController
{
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    public function __construct(ProductRepository $productRepository, ResponseFactory $responseFactory)
    {
        $this->productRepository = $productRepository;
        $this->responseFactory = $responseFactory;
    }

    public function index()
    {
        return $this->responseFactory->repond($this->isMatch(5));
    }

    private function isMatch($value): bool
    {
        return (bool) $this->productRepository->findBy($value);
    }
}
?>
-----
<?php

namespace Rector\Architecture\Tests\Rector\Class_\ConstructorInjectionToActionInjectionRector\Multiple;

use Rector\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\ProductRepository;

final class SkipNonActionMethodsController
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index(ResponseFactory $responseFactory)
    {
        return $responseFactory->repond($this->isMatch(5));
    }

    private function isMatch($value): bool
    {
        return (bool) $this->productRepository->findBy($value);
    }
}
?>
