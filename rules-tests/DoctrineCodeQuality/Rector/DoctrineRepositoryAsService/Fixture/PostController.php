<?php

namespace Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Fixture;

use Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Source\Entity\Post;
use Rector\Tests\Core\Rector\Architecture\DoctrineRepositoryAsService\Source\SymfonyController;
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

namespace Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Fixture;

use Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Source\Entity\Post;
use Rector\Tests\Core\Rector\Architecture\DoctrineRepositoryAsService\Source\SymfonyController;use Symfony\Component\HttpFoundation\Response;

final class PostController extends SymfonyController
{
    /**
     * @var \Rector\Tests\Core\Rector\Architecture\DoctrineRepositoryAsService\Source\Repository\PostRepository
     */
    private $postRepository;
    public function __construct(\Rector\Tests\Core\Rector\Architecture\DoctrineRepositoryAsService\Source\Repository\PostRepository $postRepository)
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
