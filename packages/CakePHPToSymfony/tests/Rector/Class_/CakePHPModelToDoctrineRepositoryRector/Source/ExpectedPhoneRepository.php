<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class PhoneRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;
    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(\Phone::class);
    }
    public function getAll()
    {
        return $this->findBy(['article_id' => 50]);
    }
}
