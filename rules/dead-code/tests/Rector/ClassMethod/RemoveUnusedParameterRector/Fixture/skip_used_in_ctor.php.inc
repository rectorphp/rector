<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

final class SkipUsedInCtor
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, Connection $connection)
    {
        $this->entityManager = $entityManager;

        // @see https://stackoverflow.com/a/35222045/1348344
        $connection->getConfiguration()->setSQLLogger(null);
    }
}
