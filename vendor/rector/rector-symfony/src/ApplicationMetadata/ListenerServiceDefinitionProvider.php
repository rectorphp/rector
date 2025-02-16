<?php

declare (strict_types=1);
namespace Rector\Symfony\ApplicationMetadata;

use Rector\Symfony\DataProvider\ServiceMapProvider;
use Rector\Symfony\ValueObject\ServiceDefinition;
use Rector\Symfony\ValueObject\Tag\EventListenerTag;
use Rector\Util\StringUtils;
final class ListenerServiceDefinitionProvider
{
    /**
     * @readonly
     */
    private ServiceMapProvider $serviceMapProvider;
    /**
     * @var string
     * @see https://regex101.com/r/j6SAga/1
     */
    private const SYMFONY_FAMILY_REGEX = '#^(Symfony|Sensio|Doctrine)\\b#';
    private bool $areListenerClassesLoaded = \false;
    /**
     * @var ServiceDefinition[][][]
     */
    private array $listenerClassesToEvents = [];
    public function __construct(ServiceMapProvider $serviceMapProvider)
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
            if (StringUtils::isMatch((string) $eventListener->getClass(), self::SYMFONY_FAMILY_REGEX)) {
                continue;
            }
            foreach ($eventListener->getTags() as $tag) {
                if (!$tag instanceof EventListenerTag) {
                    continue;
                }
                $eventName = $tag->getEvent();
                // fill method based on the event
                if ($tag->getMethod() === '' && \strncmp($tag->getEvent(), 'kernel.', \strlen('kernel.')) === 0) {
                    [, $event] = \explode('.', $tag->getEvent());
                    $methodName = 'onKernel' . \ucfirst($event);
                    $tag->changeMethod($methodName);
                }
                $this->listenerClassesToEvents[$eventListener->getClass()][$eventName][] = $eventListener;
            }
        }
        $this->areListenerClassesLoaded = \true;
        return $this->listenerClassesToEvents;
    }
}
