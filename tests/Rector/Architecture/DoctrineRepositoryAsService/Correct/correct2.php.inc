<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ProductRepository;

final class ProductController extends Controller
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function somethingAction(int $id): Response
    {
        $this->productRepository->findSomething($id);

        return new Response();
    }

    public function anythingAction(int $id): Response
    {
        $this->productRepository->findSomething($id);

        return new Response();
    }
}
