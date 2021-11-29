<?php

declare (strict_types=1);
namespace Rector\Symfony\ApplicationMetadata;

use Rector\Core\Util\StringUtils;
use Rector\Symfony\DataProvider\ServiceMapProvider;
use Rector\Symfony\ValueObject\ServiceDefinition;
use Rector\Symfony\ValueObject\Tag\EventListenerTag;
final class ListenerServiceDefinitionProvider
{
    /**
     * @var string
     * @see https://regex101.com/r/j6SAga/1
     */
    private const SYMFONY_FAMILY_REGEX = '#^(Symfony|Sensio|Doctrine)\\b#';
    /**
     * @var bool
     */
    private $areListenerClassesLoaded = \false;
    /**
     * @var ServiceDefinition[][][]
     */
    private $listenerClassesToEvents = [];
    /**
     * @var \Rector\Symfony\DataProvider\ServiceMapProvider
     */
    private $serviceMapProvider;
    public function __construct(\Rector\Symfony\DataProvider\ServiceMapProvider $serviceMapProvider)
    {
        $this->serviceMapProvider = $serviceMapProvider;
    }
    /**
     * @return ServiceDefinition[][][]
     */
    public function extract() : array
    {
        if ($this->areListenerClassesLoaded) {
            return $this->listenerClassesToEvents;
        }
        $serviceMap = $this->serviceMapProvider->provide();
        $eventListeners = $serviceMap->getServicesByTag('kernel.event_listener');
        foreach ($eventListeners as $eventListener) {
            // skip Symfony core listeners
            if (\Rector\Core\Util\StringUtils::isMatch((string) $eventListener->getClass(), self::SYMFONY_FAMILY_REGEX)) {
                continue;
            }
            foreach ($eventListener->getTags() as $tag) {
                if (!$tag instanceof \Rector\Symfony\ValueObject\Tag\EventListenerTag) {
                    continue;
                }
                $eventName = $tag->getEvent();
                $this->listenerClassesToEvents[$eventListener->getClass()][$eventName][] = $eventListener;
            }
        }
        $this->areListenerClassesLoaded = \true;
        return $this->listenerClassesToEvents;
    }
}
