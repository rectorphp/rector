<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class FindListWithOneArgumentRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;
    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(\FindListWithOneArgument::class);
    }
    public function getList()
    {
        return $this->repository->createQueryBuilder('f')->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
}
