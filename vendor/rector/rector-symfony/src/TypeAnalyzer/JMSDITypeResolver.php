<?php

declare (strict_types=1);
namespace Rector\Symfony\TypeAnalyzer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
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
            if ($serviceType instanceof Type) {
                return $serviceType;
            }
        }
        return new MixedType();
    }
    private function resolveServiceName(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, Property $property) : string
    {
        $serviceNameParameter = $doctrineAnnotationTagValueNode->getValue('serviceName');
        if ($serviceNameParameter instanceof ArrayItemNode && \is_string($serviceNameParameter->value)) {
            return $serviceNameParameter->value;
        }
        $arrayItemNode = $doctrineAnnotationTagValueNode->getSilentValue();
        if ($arrayItemNode instanceof ArrayItemNode && \is_string($arrayItemNode->value)) {
            return $arrayItemNode->value;
        }
        return $this->nodeNameResolver->getName($property);
    }
}
