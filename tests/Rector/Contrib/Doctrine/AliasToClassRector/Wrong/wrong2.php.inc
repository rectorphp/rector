<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use Doctrine\ORM\EntityRepository;

final class PostRepository
{
    /**
     * @var \App\Entity\Post
     */
    private $post;
    public function __construct(\Doctrine\ORM\EntityManager $entityManager)
    {
        $this->post = $entityManager->getReference('App:Post', 1);
    }
}
