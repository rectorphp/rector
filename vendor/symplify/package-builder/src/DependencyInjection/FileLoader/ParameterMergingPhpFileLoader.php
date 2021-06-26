<?php

declare (strict_types=1);
namespace RectorPrefix20210626\Symplify\PackageBuilder\DependencyInjection\FileLoader;

use RectorPrefix20210626\Symfony\Component\Config\FileLocatorInterface;
use RectorPrefix20210626\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210626\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use RectorPrefix20210626\Symplify\PackageBuilder\Yaml\ParametersMerger;
/**
 * The need:
 * - https://github.com/symfony/symfony/issues/26713
 * - https://github.com/symfony/symfony/pull/21313#issuecomment-372037445
 */
final class ParameterMergingPhpFileLoader extends \RectorPrefix20210626\Symfony\Component\DependencyInjection\Loader\PhpFileLoader
{
    /**
     * @var ParametersMerger
     */
    private $parametersMerger;
    public function __construct(\RectorPrefix20210626\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, \RectorPrefix20210626\Symfony\Component\Config\FileLocatorInterface $fileLocator)
    {
        $this->parametersMerger = new \RectorPrefix20210626\Symplify\PackageBuilder\Yaml\ParametersMerger();
        parent::__construct($containerBuilder, $fileLocator);
    }
    /**
     * Same as parent, just merging parameters instead overriding them
     *
     * @see https://github.com/symplify/symplify/pull/697
     */
    public function load($resource, string $type = null)
    {
        // get old parameters
        $parameterBag = $this->container->getParameterBag();
        $oldParameters = $parameterBag->all();
        parent::load($resource);
        foreach ($oldParameters as $key => $oldValue) {
            $newValue = $this->parametersMerger->merge($oldValue, $this->container->getParameter($key));
            $this->container->setParameter($key, $newValue);
        }
    }
}
