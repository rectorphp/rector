<?php declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\AliasToClassRector;

use Rector\Doctrine\Rector\AliasToClassRector;
use Rector\Doctrine\Tests\Rector\AliasToClassRector\Source\DoctrineEntityManager;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AliasToClassRectorTest extends AbstractRectorTestCase
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
            AliasToClassRector::class => [
                '$aliasesToNamespaces' => [
                    'App' => 'App\Entity',
                ],
                '$entityManagerClass' => DoctrineEntityManager::class,
            ],
        ];
    }
}
