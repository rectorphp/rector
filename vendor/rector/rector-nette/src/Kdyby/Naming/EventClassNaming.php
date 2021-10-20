<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\Naming;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;
final class EventClassNaming
{
    /**
     * @var string
     */
    private const EVENT = 'Event';
    /**
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\CodingStyle\Naming\ClassNaming $classNaming, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->classNaming = $classNaming;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * "App\SomeNamespace\SomeClass::onUpload" → "App\SomeNamespace\Event\SomeClassUploadEvent"
     */
    public function createEventClassNameFromMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : string
    {
        $shortEventClassName = $this->getShortEventClassName($methodCall);
        /** @var string $className */
        $className = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        return $this->prependShortClassEventWithNamespace($shortEventClassName, $className);
    }
    public function resolveEventFileLocationFromMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : string
    {
        $shortEventClassName = $this->getShortEventClassName($methodCall);
        $scope = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $directory = \dirname($scope->getFile());
        return $directory . \DIRECTORY_SEPARATOR . self::EVENT . \DIRECTORY_SEPARATOR . $shortEventClassName . '.php';
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
    private function getShortEventClassName(\PhpParser\Node\Expr\MethodCall $methodCall) : string
    {
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        /** @var string $className */
        $className = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        return $this->createShortEventClassNameFromClassAndProperty($className, $methodName);
    }
    private function prependShortClassEventWithNamespace(string $shortEventClassName, string $orinalClassName) : string
    {
        $namespaceAbove = \RectorPrefix20211020\Nette\Utils\Strings::before($orinalClassName, '\\', -1);
        return $namespaceAbove . '\\Event\\' . $shortEventClassName;
    }
    /**
     * TomatoMarket, onBuy → TomatoMarketBuyEvent
     */
    private function createShortEventClassNameFromClassAndProperty(string $class, string $property) : string
    {
        $shortClassName = $this->classNaming->getShortName($class);
        // "onMagic" => "Magic"
        $shortPropertyName = \RectorPrefix20211020\Nette\Utils\Strings::substring($property, \strlen('on'));
        return $shortClassName . $shortPropertyName . self::EVENT;
    }
}
