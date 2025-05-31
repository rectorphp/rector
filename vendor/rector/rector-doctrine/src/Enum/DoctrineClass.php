<?php

declare (strict_types=1);
namespace Rector\Doctrine\Enum;

final class DoctrineClass
{
    /**
     * @var string
     */
    public const ABSTRACT_FIXTURE = 'Doctrine\\Common\\DataFixtures\\AbstractFixture';
    /**
     * @var string
     */
    public const AS_DOCTRINE_LISTENER_ATTRIBUTE = 'Doctrine\\Bundle\\DoctrineBundle\\Attribute\\AsDoctrineListener';
    /**
     * @var string
     */
    public const EVENT_SUBSCRIBER = 'Doctrine\\Common\\EventSubscriber';
    public const EVENT_SUBSCRIBER_INTERFACE = 'Doctrine\\Bundle\\DoctrineBundle\\EventSubscriber\\EventSubscriberInterface';
    /**
     * @var string
     */
    public const COLLECTION = 'Doctrine\\Common\\Collections\\Collection';
    /**
     * @var string
     */
    public const ARRAY_COLLECTION = 'Doctrine\\Common\\Collections\\ArrayCollection';
    /**
     * @var string
     */
    public const SERVICE_DOCUMENT_REPOSITORY = 'Doctrine\\Bundle\\MongoDBBundle\\Repository\\ServiceDocumentRepository';
    /**
     * @var string
     */
    public const SERVICE_ENTITY_REPOSITORY = 'Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository';
    /**
     * @var string
     */
    public const ENTITY_REPOSITORY = 'Doctrine\\ORM\\EntityRepository';
    /**
     * @var string
     */
    public const OBJECT_REPOSITORY = 'Doctrine\\Persistence\\ObjectRepository';
    /**
     * @var string
     */
    public const ARRAY_RESULT = 'Doctrine\\DBAL\\Cache\\ArrayResult';
}
