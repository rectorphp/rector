<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector;

use Rector\CakePHPToSymfony\Rector\Class_\CakePHPModelToDoctrineRepositoryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CakePHPModelToDoctrineRepositoryRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/fixture.php.inc');

        $repositoryFilePath = $this->getTempPath() . '/ActivityRepository.php';
        $this->assertFileExists($repositoryFilePath);
        $this->assertFileEquals(__DIR__ . '/Source/ExpectedActivityRepository.php', $repositoryFilePath);
    }

    public function testThreaded(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/threaded.php.inc');

        $repositoryFilePath = $this->getTempPath() . '/PhoneRepository.php';
        $this->assertFileExists($repositoryFilePath);
        $this->assertFileEquals(__DIR__ . '/Source/ExpectedPhoneRepository.php', $repositoryFilePath);
    }

    protected function getRectorClass(): string
    {
        return CakePHPModelToDoctrineRepositoryRector::class;
    }
}
