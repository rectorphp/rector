<?php declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector;

use Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector;
use Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Source\DummyManagerRegistry;
use Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Source\DummyObjectManager;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ManagerRegistryGetManagerToEntityManagerRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/keep_different_methods.php.inc',
            __DIR__ . '/Fixture/do_not_remove_registry_on_non_get_repo_call.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ManagerRegistryGetManagerToEntityManagerRector::class => [
                '$managerRegistryClass' => DummyManagerRegistry::class,
                '$objectManagerClass' => DummyObjectManager::class,
            ],
        ];
    }
}
