<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\TypeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\Provider\CurrentFileProvider;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\Symfony\DataProvider\ServiceMapProvider;
use RectorPrefix20220606\Rector\Symfony\ValueObject\ServiceMap\ServiceMap;
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
    public function __construct(ServiceMapProvider $serviceMapProvider, PhpDocInfoFactory $phpDocInfoFactory, ReflectionProvider $reflectionProvider, NodeNameResolver $nodeNameResolver, CurrentFileProvider $currentFileProvider)
    {
        $this->serviceMapProvider = $serviceMapProvider;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->currentFileProvider = $currentFileProvider;
    }
    public function resolve(Property $property, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : Type
    {
        $serviceMap = $this->serviceMapProvider->provide();
        $serviceName = $this->resolveServiceName($doctrineAnnotationTagValueNode, $property);
        $serviceType = $this->resolveFromServiceName($serviceName, $serviceMap);
        if (!$serviceType instanceof MixedType) {
            return $serviceType;
        }
        // 3. service is in @var annotation
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $varType = $phpDocInfo->getVarType();
        if (!$varType instanceof MixedType) {
            return $varType;
        }
        // the @var is missing and service name was not found → report it
        $this->reportServiceNotFound($serviceName);
        return new MixedType();
    }
    private function reportServiceNotFound(?string $serviceName) : void
    {
        if ($serviceName !== null) {
            return;
        }
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            throw new ShouldNotHappenException();
        }
        $errorMessage = \sprintf('Service "%s" was not found in DI Container of your Symfony App.', $serviceName);
        throw new ShouldNotHappenException($errorMessage);
    }
    private function resolveFromServiceName(string $serviceName, ServiceMap $serviceMap) : Type
    {
        // 1. service name-type
        if ($this->reflectionProvider->hasClass($serviceName)) {
            // single class service
            return new ObjectType($serviceName);
        }
        // 2. service name
        if ($serviceMap->hasService($serviceName)) {
            $serviceType = $serviceMap->getServiceType($serviceName);
            if ($serviceType !== null) {
                return $serviceType;
            }
        }
        return new MixedType();
    }
    private function resolveServiceName(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, Property $property) : string
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
