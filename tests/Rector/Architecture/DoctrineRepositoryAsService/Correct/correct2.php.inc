<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

final class PostController extends Controller
{
    /**
     * @var \AppBundle\Repository\PostRepository
     */
    private $postRepository;
    public function __construct(\AppBundle\Repository\PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }
    public function somethingAction(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $this->postRepository->findSomething($id);

        return new Response();
    }

    public function anythingAction(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $this->postRepository->findSomething($id);

        return new Response();
    }
}
