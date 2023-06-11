<?php

declare (strict_types=1);
namespace Rector\Symfony\DataProvider;

use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Symfony\ValueObject\ServiceMap\ServiceMap;
use Rector\Symfony\ValueObjectFactory\ServiceMapFactory;
/**
 * Inspired by https://github.com/phpstan/phpstan-symfony/tree/master/src/Symfony
 */
final class ServiceMapProvider
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Rector\Symfony\ValueObjectFactory\ServiceMapFactory
     */
    private $serviceMapFactory;
    /**
     * @var \Rector\Symfony\ValueObject\ServiceMap\ServiceMap|null
     */
    private $serviceMap;
    public function __construct(ParameterProvider $parameterProvider, ServiceMapFactory $serviceMapFactory, ?ServiceMap $serviceMap = null)
    {
        $this->parameterProvider = $parameterProvider;
        $this->serviceMapFactory = $serviceMapFactory;
        $this->serviceMap = $serviceMap;
    }
    public function provide() : ServiceMap
    {
        if ($this->serviceMap instanceof ServiceMap) {
            return $this->serviceMap;
        }
        $symfonyContainerXmlPath = (string) $this->parameterProvider->provideParameter(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER);
        if ($symfonyContainerXmlPath === '') {
            $this->serviceMap = $this->serviceMapFactory->createEmpty();
        } else {
            $this->serviceMap = $this->serviceMapFactory->createFromFileContent($symfonyContainerXmlPath);
        }
        return $this->serviceMap;
    }
}
