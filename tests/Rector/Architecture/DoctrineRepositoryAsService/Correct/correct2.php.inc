<?php declare(strict_types=1);

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Repository\PostRepository;

final class PostController extends Controller
{
    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function somethingAction(int $id): Response
    {
        $this->postRepository->findSomething($id);

        return new Response();
    }

    public function anythingAction(int $id): Response
    {
        $this->postRepository->findSomething($id);

        return new Response();
    }
}
