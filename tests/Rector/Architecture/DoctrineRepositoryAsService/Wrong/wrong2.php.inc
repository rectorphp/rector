<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

final class PostController extends Controller
{
    public function somethingAction(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AppBundle:Post')->findSomething($id);

        return new Response();
    }

    public function anythingAction(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository(Post::class)->findSomething($id);

        return new Response();
    }
}
