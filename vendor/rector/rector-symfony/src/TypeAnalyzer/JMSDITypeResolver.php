<?php

declare (strict_types=1);
namespace Rector\Symfony\TypeAnalyzer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Symfony\DataProvider\ServiceMapProvider;
use Rector\Symfony\ValueObject\ServiceMap\ServiceMap;
final class JMSDITypeResolver
{
    /**
     * @readonly
     * @var \Rector\Symfony\DataProvider\ServiceMapProvider
     */
    private $serviceMapProvider;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    public function __construct(\Rector\Symfony\DataProvider\ServiceMapProvider $serviceMapProvider, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider)
    {
        $this->serviceMapProvider = $serviceMapProvider;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->currentFileProvider = $currentFileProvider;
    }
    public function resolve(\PhpParser\Node\Stmt\Property $property, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : \PHPStan\Type\Type
    {
        $serviceMap = $this->serviceMapProvider->provide();
        $serviceName = $this->resolveServiceName($doctrineAnnotationTagValueNode, $property);
        $serviceType = $this->resolveFromServiceName($serviceName, $serviceMap);
        if (!$serviceType instanceof \PHPStan\Type\MixedType) {
            return $serviceType;
        }
        // 3. service is in @var annotation
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $varType = $phpDocInfo->getVarType();
        if (!$varType instanceof \PHPStan\Type\MixedType) {
            return $varType;
        }
        // the @var is missing and service name was not found → report it
        $this->reportServiceNotFound($serviceName);
        return new \PHPStan\Type\MixedType();
    }
    private function reportServiceNotFound(?string $serviceName) : void
    {
        if ($serviceName !== null) {
            return;
        }
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof \Rector\Core\ValueObject\Application\File) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $errorMessage = \sprintf('Service "%s" was not found in DI Container of your Symfony App.', $serviceName);
        throw new \Rector\Core\Exception\ShouldNotHappenException($errorMessage);
    }
    private function resolveFromServiceName(string $serviceName, \Rector\Symfony\ValueObject\ServiceMap\ServiceMap $serviceMap) : \PHPStan\Type\Type
    {
        // 1. service name-type
        if ($this->reflectionProvider->hasClass($serviceName)) {
            // single class service
            return new \PHPStan\Type\ObjectType($serviceName);
        }
        // 2. service name
        if ($serviceMap->hasService($serviceName)) {
            $serviceType = $serviceMap->getServiceType($serviceName);
            if ($serviceType !== null) {
                return $serviceType;
            }
        }
        return new \PHPStan\Type\MixedType();
    }
    private function resolveServiceName(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, \PhpParser\Node\Stmt\Property $property) : string
    {
        $serviceNameParameter = $doctrineAnnotationTagValueNode->getValueWithoutQuotes('serviceName');
        if (\is_string($serviceNameParameter)) {
            return $serviceNameParameter;
        }
        $silentValue = $doctrineAnnotationTagValueNode->getSilentValue();
        if (\is_string($silentValue)) {
            return $silentValue;
        }
        return $this->nodeNameResolver->getName($property);
    }
}
