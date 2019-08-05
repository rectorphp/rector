<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Source\SomeEntityProvider;

class SomeClassOnlyUsingTrait
{
    use SkipTraitComplex;
}

trait SkipTraitComplex
{
    public function run(): void
    {
        $provider = self::getProvider();

        $entities = $provider->provideMultiple([]);

        foreach ($entities as $entity) {
            $entity->getStatus();
        }
    }

    public static function getProvider(): SomeStatusEntityProvider
    {
        return new SomeStatusEntityProvider;
    }
}

/**
 * @ORM\Entity
 */
class SomeStatusEntity
{
    /**
     * @var string
     */
    private $status;

    public function getStatus(): string
    {
        return $this->status;
    }
}

class SomeStatusEntityProvider
{
    /**
     * @return SomeStatusEntity[]
     */
    public function provideMultiple($items): array
    {
        return $items;
    }
}