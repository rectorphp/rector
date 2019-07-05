<?php

namespace Rector\Architecture\Tests\Rector\Class_\ConstructorInjectionToActionInjectionRector\Fixture;

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

    public function index()
    {
        return $this->isMatch(5);
    }

    private function isMatch($value): bool
    {
        return (bool) $this->productRepository->findBy($value);
    }
}
