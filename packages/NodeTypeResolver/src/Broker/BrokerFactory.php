<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Broker;

use PHPStan\Broker\Broker;
use PHPStan\Broker\BrokerFactory as OriginalBrokerFactory;
use PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\Php\PhpClassReflectionExtension;
use PHPStan\Reflection\PhpDefect\PhpDefectClassReflectionExtension;
use PHPStan\Type\FileTypeMapper;
use Rector\NodeTypeResolver\Reflection\FunctionReflectionFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Only mimics original @see \PHPStan\Broker\BrokerFactory with Symfony DI Container.
 */
final class BrokerFactory
{
    /**
     * @var ContainerInterface|ContainerBuilder
     */
    private $container;

    /**
     * @var FunctionReflectionFactory
     */
    private $functionReflectionFactory;

    /**
     * @var FileTypeMapper
     */
    private $fileTypeMapper;

    public function __construct(
        ContainerInterface $container,
        FunctionReflectionFactory $functionReflectionFactory,
        FileTypeMapper $fileTypeMapper
    ) {
        $this->container = $container;
        $this->functionReflectionFactory = $functionReflectionFactory;
        $this->fileTypeMapper = $fileTypeMapper;
    }

    public function create(): Broker
    {
        $tagToService = function (array $tags) {
            return array_map(function (string $serviceName) {
                return $this->container->get($serviceName);
            }, array_keys($tags));
        };

        // @todo: require in ctor
        $phpClassReflectionExtension = $this->container->get(PhpClassReflectionExtension::class);
        $annotationsPropertiesClassReflectionExtension = $this->container->get(AnnotationsPropertiesClassReflectionExtension::class);
        $annotationsMethodsClassReflectionExtension = $this->container->get(AnnotationsMethodsClassReflectionExtension::class);
        $phpDefectClassReflectionExtension = $this->container->get(PhpDefectClassReflectionExtension::class);

        dump('EEA');
        die;

        dump($tagToService($this->container->findTaggedServiceIds(OriginalBrokerFactory::PROPERTIES_CLASS_REFLECTION_EXTENSION_TAG)));

        die;


        $propertiesClassReflectionExtensions = array_merge(
            [$phpClassReflectionExtension, $phpDefectClassReflectionExtension],
            $tagToService($this->container->findTaggedServiceIds(OriginalBrokerFactory::PROPERTIES_CLASS_REFLECTION_EXTENSION_TAG)),
            [$annotationsPropertiesClassReflectionExtension]
        );

        $methodsClassReflectionExtensions = array_merge(
            [$phpClassReflectionExtension],
            $tagToService($this->container->findTaggedServiceIds(OriginalBrokerFactory::METHODS_CLASS_REFLECTION_EXTENSION_TAG)),
            [$annotationsMethodsClassReflectionExtension]
        );

        $dynamicMethodReturnTypeExtensions = $tagToService(
            $this->container->findTaggedServiceIds(OriginalBrokerFactory::DYNAMIC_METHOD_RETURN_TYPE_EXTENSION_TAG));
            $dynamicStaticMethodReturnTypeExtensions = $tagToService($this->container->findTaggedServiceIds(OriginalBrokerFactory::DYNAMIC_STATIC_METHOD_RETURN_TYPE_EXTENSION_TAG)
        );


        return new Broker(
            $propertiesClassReflectionExtensions,
            $methodsClassReflectionExtensions,
            $dynamicMethodReturnTypeExtensions,
            $dynamicStaticMethodReturnTypeExtensions,
            $this->functionReflectionFactory,
            $this->fileTypeMapper
        );
    }
}
