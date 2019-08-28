<?php declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\MethodCall\EntityAliasToClassConstantReferenceRector;

use Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector;
use Rector\Doctrine\Tests\Rector\MethodCall\EntityAliasToClassConstantReferenceRector\Source\DoctrineEntityManager;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class EntityAliasToClassConstantReferenceRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            EntityAliasToClassConstantReferenceRector::class => [
                '$aliasesToNamespaces' => [
                    'App' => 'App\Entity',
                ],
                '$entityManagerClass' => DoctrineEntityManager::class,
            ],
        ];
    }
}
