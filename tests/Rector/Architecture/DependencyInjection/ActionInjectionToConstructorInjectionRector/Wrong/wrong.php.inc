<?php declare(strict_types=1);

final class SomeController
{
    public function default(ProductRepository $productRepository)
    {
        $products = $productRepository->fetchAll();
    }
}
