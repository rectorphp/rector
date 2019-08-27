<?php declare(strict_types=1);

namespace Rector\Doctrine\ValueObject;

final class DoctrineClass
{
    /**
     * @var string
     */
    public const MANAGER_REGISTRY = 'Doctrine\Common\Persistence\ManagerRegistry';

    /**
     * @var string
     */
    public const ENTITY_MANAGER = 'Doctrine\ORM\EntityManagerInterface';

    /**
     * @var string
     */
    public const OBJECT_MANAGER = 'Doctrine\Common\Persistence\ObjectManager';
}
