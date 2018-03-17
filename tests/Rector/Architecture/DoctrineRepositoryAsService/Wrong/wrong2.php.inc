<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

final class ProductController extends Controller
{
    public function somethingAction(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('SomethingBundle:Product')->findSomething($id);

        return new Response();
    }

    public function anythingAction(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository(Product::class)->findSomething($id);

        return new Response();
    }
}
