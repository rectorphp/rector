<?php

declare (strict_types=1);
namespace Rector\Symfony\DataProvider;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Symfony\ValueObject\ServiceMap\ServiceMap;
use Rector\Symfony\ValueObjectFactory\ServiceMapFactory;
/**
 * Inspired by https://github.com/phpstan/phpstan-symfony/tree/master/src/Symfony
 */
final class ServiceMapProvider
{
    /**
     * @readonly
     * @var \Rector\Symfony\ValueObjectFactory\ServiceMapFactory
     */
    private $serviceMapFactory;
    /**
     * @var \Rector\Symfony\ValueObject\ServiceMap\ServiceMap|null
     */
    private $serviceMap;
    public function __construct(ServiceMapFactory $serviceMapFactory, ?ServiceMap $serviceMap = null)
    {
        $this->serviceMapFactory = $serviceMapFactory;
        $this->serviceMap = $serviceMap;
    }
    public function provide() : ServiceMap
    {
        // avoid caching in tests
        if (\defined('PHPUNIT_COMPOSER_INSTALL')) {
            $this->serviceMap = null;
        }
        if ($this->serviceMap instanceof ServiceMap) {
            return $this->serviceMap;
        }
        if (SimpleParameterProvider::hasParameter(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER)) {
            $symfonyContainerXmlPath = SimpleParameterProvider::provideStringParameter(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER);
            $this->serviceMap = $this->serviceMapFactory->createFromFileContent($symfonyContainerXmlPath);
        } else {
            $this->serviceMap = $this->serviceMapFactory->createEmpty();
        }
        return $this->serviceMap;
    }
}
