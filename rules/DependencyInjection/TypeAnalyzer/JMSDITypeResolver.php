<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\TypeAnalyzer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Symfony\DataProvider\ServiceMapProvider;
use Rector\Symfony\ValueObject\ServiceMap\ServiceMap;

final class JMSDITypeResolver
{
    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    /**
     * @var ServiceMapProvider
     */
    private $serviceMapProvider;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var CurrentFileProvider
     */
    private $currentFileProvider;

    public function __construct(
        ErrorAndDiffCollector $errorAndDiffCollector,
        ServiceMapProvider $serviceMapProvider,
        PhpDocInfoFactory $phpDocInfoFactory,
        ReflectionProvider $reflectionProvider,
        NodeNameResolver $nodeNameResolver,
        CurrentFileProvider $currentFileProvider
    ) {
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->serviceMapProvider = $serviceMapProvider;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->currentFileProvider = $currentFileProvider;
    }

    public function resolve(
        Property $property,
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode
    ): Type {
        $serviceMap = $this->serviceMapProvider->provide();

        $serviceName = $doctrineAnnotationTagValueNode->getValueWithoutQuotes(
            'serviceName'
        ) ?: $doctrineAnnotationTagValueNode->getSilentValue() ?: $this->nodeNameResolver->getName($property);

        if ($serviceName) {
            $serviceType = $this->resolveFromServiceName($serviceName, $serviceMap);
            if (! $serviceType instanceof MixedType) {
                return $serviceType;
            }
        }

        // 3. service is in @var annotation
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $varType = $phpDocInfo->getVarType();
        if (! $varType instanceof MixedType) {
            return $varType;
        }

        // the @var is missing and service name was not found → report it
        $this->reportServiceNotFound($serviceName, $property);

        return new MixedType();
    }

    private function reportServiceNotFound(?string $serviceName, Property $property): void
    {
        if ($serviceName !== null) {
            return;
        }

        $file = $this->currentFileProvider->getFile();

        $smartFileInfo = $file->getSmartFileInfo();
        $errorMessage = sprintf('Service "%s" was not found in DI Container of your Symfony App.', $serviceName);

        $this->errorAndDiffCollector->addErrorWithRectorClassMessageAndFileInfo(
            self::class,
            $errorMessage,
            $smartFileInfo
        );
    }

    private function resolveFromServiceName(string $serviceName, ServiceMap $serviceMap): Type
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
}
