<?php declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

final class PostRepository
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    public function __construct(\Doctrine\ORM\EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function select()
    {
        return $this->em
            ->createQueryBuilder('post')
            ->select('post, comment')
            ->from('App:Post', 'post')
            ->join('App:Comment','comment')
            ->getQuery()
            ->getResult();
    }

    public function update()
    {
        return $this->em
            ->createQueryBuilder('post')
            ->update('App:Post', 'post')
            ->set('post.views', 'post.views + 1')
            ->getQuery()
            ->execute();
    }
}
