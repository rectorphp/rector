<?php declare(strict_types=1);

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
