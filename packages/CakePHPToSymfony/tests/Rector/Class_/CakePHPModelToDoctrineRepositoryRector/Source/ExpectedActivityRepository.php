<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class ActivityRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;
    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(\Activity::class);
    }
    public function getAll()
    {
        $result = $this->repository->findAll();
        return $result;
    }
    public function getOne()
    {
        $result = $this->findOneBy(['revision_number' => $versionId, 'document_id' => $documentId], 'created DESC');
        return $result;
    }
}
