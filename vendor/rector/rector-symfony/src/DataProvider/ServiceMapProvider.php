<?php

declare (strict_types=1);
namespace Rector\Symfony\DataProvider;

use Rector\Symfony\ValueObject\ServiceMap\ServiceMap;
use Rector\Symfony\ValueObjectFactory\ServiceMapFactory;
use RectorPrefix20211020\Symplify\PackageBuilder\Parameter\ParameterProvider;
/**
 * Inspired by https://github.com/phpstan/phpstan-symfony/tree/master/src/Symfony
 */
final class ServiceMapProvider
{
    /**
     * @var string
     */
    private const SYMFONY_CONTAINER_XML_PATH_PARAMETER = 'symfony_container_xml_path';
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var \Rector\Symfony\ValueObjectFactory\ServiceMapFactory
     */
    private $serviceMapFactory;
    public function __construct(\RectorPrefix20211020\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \Rector\Symfony\ValueObjectFactory\ServiceMapFactory $serviceMapFactory)
    {
        $this->parameterProvider = $parameterProvider;
        $this->serviceMapFactory = $serviceMapFactory;
    }
    public function provide() : \Rector\Symfony\ValueObject\ServiceMap\ServiceMap
    {
        $symfonyContainerXmlPath = (string) $this->parameterProvider->provideParameter(self::SYMFONY_CONTAINER_XML_PATH_PARAMETER);
        if ($symfonyContainerXmlPath === '') {
            return $this->serviceMapFactory->createEmpty();
        }
        return $this->serviceMapFactory->createFromFileContent($symfonyContainerXmlPath);
    }
}
