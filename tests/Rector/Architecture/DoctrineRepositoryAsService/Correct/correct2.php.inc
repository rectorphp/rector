<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Wrong;

use AppBundle\Entity\Post;
use Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\SymfonyController;
use Symfony\Component\HttpFoundation\Response;

final class PostController extends SymfonyController
{
    /**
     * @var \AppBundle\Repository\PostRepository
     */
    private $postRepository;
    public function __construct(\AppBundle\Repository\PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }
    public function anythingAction(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $this->postRepository->findSomething($id);

        return new Response();
    }
}
