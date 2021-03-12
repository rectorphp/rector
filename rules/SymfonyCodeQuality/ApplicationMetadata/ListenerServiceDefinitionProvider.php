<?php

declare(strict_types=1);

namespace Rector\SymfonyCodeQuality\ApplicationMetadata;

use Nette\Utils\Strings;
use Rector\Symfony\ServiceMapProvider;
use Rector\Symfony\ValueObject\ServiceDefinition;
use Rector\Symfony\ValueObject\Tag\EventListenerTag;

final class ListenerServiceDefinitionProvider
{
    /**
     * @var string
     * @see https://regex101.com/r/j6SAga/1
     */
    private const SYMFONY_FAMILY_REGEX = '#^(Symfony|Sensio|Doctrine)\b#';

    /**
     * @var bool
     */
    private $areListenerClassesLoaded = false;

    /**
     * @var ServiceDefinition[][][]
     */
    private $listenerClassesToEvents = [];

    /**
     * @var ServiceMapProvider
     */
    private $applicationServiceMapProvider;

    public function __construct(ServiceMapProvider $applicationServiceMapProvider)
    {
        $this->applicationServiceMapProvider = $applicationServiceMapProvider;
    }

    /**
     * @return ServiceDefinition[][][]
     */
    public function extract(): array
    {
        if ($this->areListenerClassesLoaded) {
            return $this->listenerClassesToEvents;
        }

        $serviceMap = $this->applicationServiceMapProvider->provide();
        $eventListeners = $serviceMap->getServicesByTag('kernel.event_listener');

        foreach ($eventListeners as $eventListener) {
            // skip Symfony core listeners
            if (Strings::match((string) $eventListener->getClass(), self::SYMFONY_FAMILY_REGEX)) {
                continue;
            }

            foreach ($eventListener->getTags() as $tag) {
                if (! $tag instanceof EventListenerTag) {
                    continue;
                }

                $eventName = $tag->getEvent();
                $this->listenerClassesToEvents[$eventListener->getClass()][$eventName][] = $eventListener;
            }
        }

        $this->areListenerClassesLoaded = true;

        return $this->listenerClassesToEvents;
    }
}
