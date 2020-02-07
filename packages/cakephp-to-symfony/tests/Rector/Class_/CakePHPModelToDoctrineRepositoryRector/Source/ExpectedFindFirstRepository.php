<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\Fixture;

class FindFirstRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;
    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(\FindFirst::class);
    }
    public function getOne()
    {
        $result = $this->repository->findOneBy(['revision_number' => $versionId, 'document_id' => $documentId], 'created DESC');
        return $result;
    }
}
