<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\DoctrineRepositoryAsService\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

final class SkipTestCase extends TestCase
{
    public function testGetThrowsExceptionIfNotFound(): void
    {
        $entityManagerMock = $this->prophesize(EntityManagerInterface::class);

        $entityManagerMock->getRepository(Argument::any());
    }
}
