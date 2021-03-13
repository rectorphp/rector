<?php

namespace Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Fixture;

use Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Source\Entity\Post;
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

namespace Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Fixture;

use Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Source\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class PostController extends AbstractController
{
    public function __construct(private \Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Source\Repository\PostRepository $postRepository)
    {
    }
    public function anythingAction(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $this->postRepository->find($id);

        return new Response();
    }
}

?>
