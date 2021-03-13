<?php

namespace Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Fixture;

use Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Source\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class PostController extends AbstractController
{
    public function anythingAction(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository(Post::class)->find($id);

        return new Response();
    }
}

?>
-----
<?php

namespace Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Fixture;

use Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Source\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class PostController extends AbstractController
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
        $this->postRepository->find($id);

        return new Response();
    }
}

?>
