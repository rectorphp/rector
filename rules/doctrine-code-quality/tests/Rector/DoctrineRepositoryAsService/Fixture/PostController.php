<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\DoctrineRepositoryAsService\Fixture;

use Rector\Core\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\Entity\Post;
use Rector\Core\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\SymfonyController;
use Symfony\Component\HttpFoundation\Response;

final class PostController extends SymfonyController
{
    public function anythingAction(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository(Post::class)->findSomething($id);

        return new Response();
    }
}

?>
-----
<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\DoctrineRepositoryAsService\Fixture;

use Rector\Core\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\Entity\Post;
use Rector\Core\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\SymfonyController;
use Symfony\Component\HttpFoundation\Response;

final class PostController extends SymfonyController
{
    /**
     * @var \Rector\Core\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\Repository\PostRepository
     */
    private $postRepository;
    public function __construct(\Rector\Core\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\Repository\PostRepository $postRepository)
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

?>
