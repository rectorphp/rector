<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Naming;

use Nette\Utils\Strings;
use PhpParser\Node\Expr\MethodCall;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

final class EventClassNaming
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(NodeNameResolver $nodeNameResolver, ClassNaming $classNaming)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classNaming = $classNaming;
    }

    /**
     * "App\SomeNamespace\SomeClass::onUpload"
     * ↓
     * "App\SomeNamespace\Event\SomeClassUploadEvent"
     */
    public function createEventClassNameFromMethodCall(MethodCall $methodCall): string
    {
        $shortEventClassName = $this->getShortEventClassName($methodCall);

        /** @var string $className */
        $className = $methodCall->getAttribute(AttributeKey::CLASS_NAME);

        return $this->prependShortClassEventWithNamespace($shortEventClassName, $className);
    }

    public function resolveEventFileLocation(MethodCall $methodCall): string
    {
        $shortEventClassName = $this->getShortEventClassName($methodCall);

        /** @var SmartFileInfo $fileInfo */
        $fileInfo = $methodCall->getAttribute(AttributeKey::FILE_INFO);

        return $fileInfo->getPath() . DIRECTORY_SEPARATOR . 'Event' . DIRECTORY_SEPARATOR . $shortEventClassName . '.php';
    }

    public function createEventClassNameFromClassAndProperty(string $className, string $methodName): string
    {
        $shortEventClass = $this->createShortEventClassNameFromClassAndProperty($className, $methodName);

        return $this->prependShortClassEventWithNamespace($shortEventClass, $className);
    }

    public function createEventClassNameFromClassPropertyReference(string $classAndPropertyName): string
    {
        [$class, $property] = Strings::split($classAndPropertyName, '#::#');

        $shortEventClass = $this->createShortEventClassNameFromClassAndProperty($class, $property);

        return $this->prependShortClassEventWithNamespace($shortEventClass, $class);
    }

    /**
     * TomatoMarket, onBuy
     * ↓
     * TomatoMarketBuyEvent
     */
    private function createShortEventClassNameFromClassAndProperty(string $class, string $property): string
    {
        $shortClassName = $this->classNaming->getShortName($class);

        // "onMagic" => "Magic"
        $shortPropertyName = Strings::substring($property, strlen('on'));

        return $shortClassName . $shortPropertyName . 'Event';
    }

    private function getShortEventClassName(MethodCall $methodCall): string
    {
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($methodCall->name);

        /** @var string $className */
        $className = $methodCall->getAttribute(AttributeKey::CLASS_NAME);

        return $this->createShortEventClassNameFromClassAndProperty($className, $methodName);
    }

    private function prependShortClassEventWithNamespace(string $shortEventClassName, string $orinalClassName): string
    {
        $namespaceAbove = Strings::before($orinalClassName, '\\', -1);

        return $namespaceAbove . '\\Event\\' . $shortEventClassName;
    }
}
