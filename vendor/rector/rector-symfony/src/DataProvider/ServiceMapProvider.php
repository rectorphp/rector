<?php

declare (strict_types=1);
namespace Rector\Symfony\DataProvider;

use Rector\Core\Configuration\Option;
use Rector\Symfony\ValueObject\ServiceMap\ServiceMap;
use Rector\Symfony\ValueObjectFactory\ServiceMapFactory;
use RectorPrefix202208\Symplify\PackageBuilder\Parameter\ParameterProvider;
/**
 * Inspired by https://github.com/phpstan/phpstan-symfony/tree/master/src/Symfony
 */
final class ServiceMapProvider
{
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Rector\Symfony\ValueObjectFactory\ServiceMapFactory
     */
    private $serviceMapFactory;
    public function __construct(ParameterProvider $parameterProvider, ServiceMapFactory $serviceMapFactory)
    {
        $this->parameterProvider = $parameterProvider;
        $this->serviceMapFactory = $serviceMapFactory;
    }
    public function provide() : ServiceMap
    {
        $symfonyContainerXmlPath = (string) $this->parameterProvider->provideParameter(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER);
        if ($symfonyContainerXmlPath === '') {
            return $this->serviceMapFactory->createEmpty();
        }
        return $this->serviceMapFactory->createFromFileContent($symfonyContainerXmlPath);
    }
}
