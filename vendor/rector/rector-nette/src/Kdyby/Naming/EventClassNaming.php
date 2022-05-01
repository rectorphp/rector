<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\Naming;

use RectorPrefix20220501\Nette\Utils\Strings;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\SmartFileSystem\SmartFileInfo;
final class EventClassNaming
{
    /**
     * @var string
     */
    private const EVENT = 'Event';
    /**
     * @readonly
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\CodingStyle\Naming\ClassNaming $classNaming, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->classNaming = $classNaming;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function resolveEventFileLocationFromClassNameAndFileInfo(string $className, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : string
    {
        $shortClassName = $this->nodeNameResolver->getShortName($className);
        return $smartFileInfo->getPath() . \DIRECTORY_SEPARATOR . self::EVENT . \DIRECTORY_SEPARATOR . $shortClassName . '.php';
    }
    public function createEventClassNameFromClassAndProperty(string $className, string $methodName) : string
    {
        $shortEventClass = $this->createShortEventClassNameFromClassAndProperty($className, $methodName);
        return $this->prependShortClassEventWithNamespace($shortEventClass, $className);
    }
    public function createEventClassNameFromClassPropertyReference(string $classAndPropertyName) : string
    {
        [$class, $property] = \explode('::', $classAndPropertyName);
        $shortEventClass = $this->createShortEventClassNameFromClassAndProperty($class, $property);
        return $this->prependShortClassEventWithNamespace($shortEventClass, $class);
    }
    private function prependShortClassEventWithNamespace(string $shortEventClassName, string $orinalClassName) : string
    {
        $namespaceAbove = \RectorPrefix20220501\Nette\Utils\Strings::before($orinalClassName, '\\', -1);
        return $namespaceAbove . '\\Event\\' . $shortEventClassName;
    }
    /**
     * TomatoMarket, onBuy → TomatoMarketBuyEvent
     */
    private function createShortEventClassNameFromClassAndProperty(string $class, string $property) : string
    {
        $shortClassName = $this->classNaming->getShortName($class);
        // "onMagic" => "Magic"
        $shortPropertyName = \RectorPrefix20220501\Nette\Utils\Strings::substring($property, \strlen('on'));
        return $shortClassName . $shortPropertyName . self::EVENT;
    }
}
