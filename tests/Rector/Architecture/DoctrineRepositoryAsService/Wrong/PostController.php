<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Wrong;

use AppBundle\Entity\Post;
use Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\SymfonyController;
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
