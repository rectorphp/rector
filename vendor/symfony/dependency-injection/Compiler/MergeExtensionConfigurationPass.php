<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler;

use RectorPrefix20220418\Symfony\Component\Config\Definition\BaseNode;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\LogicException;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Extension\Extension;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
/**
 * Merges extension configs into the container builder.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class MergeExtensionConfigurationPass implements \RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(\RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $parameters = $container->getParameterBag()->all();
        $definitions = $container->getDefinitions();
        $aliases = $container->getAliases();
        $exprLangProviders = $container->getExpressionLanguageProviders();
        $configAvailable = \class_exists(\RectorPrefix20220418\Symfony\Component\Config\Definition\BaseNode::class);
        foreach ($container->getExtensions() as $extension) {
            if ($extension instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface) {
                $extension->prepend($container);
            }
        }
        foreach ($container->getExtensions() as $name => $extension) {
            if (!($config = $container->getExtensionConfig($name))) {
                // this extension was not called
                continue;
            }
            $resolvingBag = $container->getParameterBag();
            if ($resolvingBag instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag && $extension instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\Extension\Extension) {
                // create a dedicated bag so that we can track env vars per-extension
                $resolvingBag = new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationParameterBag($resolvingBag);
                if ($configAvailable) {
                    \RectorPrefix20220418\Symfony\Component\Config\Definition\BaseNode::setPlaceholderUniquePrefix($resolvingBag->getEnvPlaceholderUniquePrefix());
                }
            }
            $config = $resolvingBag->resolveValue($config);
            try {
                $tmpContainer = new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationContainerBuilder($extension, $resolvingBag);
                $tmpContainer->setResourceTracking($container->isTrackingResources());
                $tmpContainer->addObjectResource($extension);
                if ($extension instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface && null !== ($configuration = $extension->getConfiguration($config, $tmpContainer))) {
                    $tmpContainer->addObjectResource($configuration);
                }
                foreach ($exprLangProviders as $provider) {
                    $tmpContainer->addExpressionLanguageProvider($provider);
                }
                $extension->load($config, $tmpContainer);
            } catch (\Exception $e) {
                if ($resolvingBag instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationParameterBag) {
                    $container->getParameterBag()->mergeEnvPlaceholders($resolvingBag);
                }
                throw $e;
            }
            if ($resolvingBag instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationParameterBag) {
                // don't keep track of env vars that are *overridden* when configs are merged
                $resolvingBag->freezeAfterProcessing($extension, $tmpContainer);
            }
            $container->merge($tmpContainer);
            $container->getParameterBag()->add($parameters);
        }
        $container->addDefinitions($definitions);
        $container->addAliases($aliases);
    }
}
/**
 * @internal
 */
class MergeExtensionConfigurationParameterBag extends \RectorPrefix20220418\Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag
{
    /**
     * @var mixed[]
     */
    private $processedEnvPlaceholders;
    public function __construct(parent $parameterBag)
    {
        parent::__construct($parameterBag->all());
        $this->mergeEnvPlaceholders($parameterBag);
    }
    public function freezeAfterProcessing(\RectorPrefix20220418\Symfony\Component\DependencyInjection\Extension\Extension $extension, \RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        if (!($config = $extension->getProcessedConfigs())) {
            // Extension::processConfiguration() wasn't called, we cannot know how configs were merged
            return;
        }
        $this->processedEnvPlaceholders = [];
        // serialize config and container to catch env vars nested in object graphs
        $config = \serialize($config) . \serialize($container->getDefinitions()) . \serialize($container->getAliases()) . \serialize($container->getParameterBag()->all());
        foreach (parent::getEnvPlaceholders() as $env => $placeholders) {
            foreach ($placeholders as $placeholder) {
                if (\false !== \stripos($config, $placeholder)) {
                    $this->processedEnvPlaceholders[$env] = $placeholders;
                    break;
                }
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getEnvPlaceholders() : array
    {
        return $this->processedEnvPlaceholders ?? parent::getEnvPlaceholders();
    }
    public function getUnusedEnvPlaceholders() : array
    {
        return !isset($this->processedEnvPlaceholders) ? [] : \array_diff_key(parent::getEnvPlaceholders(), $this->processedEnvPlaceholders);
    }
}
/**
 * A container builder preventing using methods that wouldn't have any effect from extensions.
 *
 * @internal
 */
class MergeExtensionConfigurationContainerBuilder extends \RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder
{
    /**
     * @var string
     */
    private $extensionClass;
    public function __construct(\RectorPrefix20220418\Symfony\Component\DependencyInjection\Extension\ExtensionInterface $extension, \RectorPrefix20220418\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag = null)
    {
        parent::__construct($parameterBag);
        $this->extensionClass = \get_class($extension);
    }
    /**
     * {@inheritdoc}
     * @return $this
     */
    public function addCompilerPass(\RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface $pass, string $type = \RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, int $priority = 0)
    {
        throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\LogicException(\sprintf('You cannot add compiler pass "%s" from extension "%s". Compiler passes must be registered before the container is compiled.', \get_debug_type($pass), $this->extensionClass));
    }
    /**
     * {@inheritdoc}
     */
    public function registerExtension(\RectorPrefix20220418\Symfony\Component\DependencyInjection\Extension\ExtensionInterface $extension)
    {
        throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\LogicException(\sprintf('You cannot register extension "%s" from "%s". Extensions must be registered before the container is compiled.', \get_debug_type($extension), $this->extensionClass));
    }
    /**
     * {@inheritdoc}
     */
    public function compile(bool $resolveEnvPlaceholders = \false)
    {
        throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\LogicException(\sprintf('Cannot compile the container in extension "%s".', $this->extensionClass));
    }
    /**
     * {@inheritdoc}
     * @param bool|string $format
     * @param mixed $value
     * @return mixed
     */
    public function resolveEnvPlaceholders($value, $format = null, array &$usedEnvs = null)
    {
        if (\true !== $format || !\is_string($value)) {
            return parent::resolveEnvPlaceholders($value, $format, $usedEnvs);
        }
        $bag = $this->getParameterBag();
        $value = $bag->resolveValue($value);
        if (!$bag instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag) {
            return parent::resolveEnvPlaceholders($value, $format, $usedEnvs);
        }
        foreach ($bag->getEnvPlaceholders() as $env => $placeholders) {
            if (\strpos($env, ':') === \false) {
                continue;
            }
            foreach ($placeholders as $placeholder) {
                if (\false !== \stripos($value, $placeholder)) {
                    throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Using a cast in "env(%s)" is incompatible with resolution at compile time in "%s". The logic in the extension should be moved to a compiler pass, or an env parameter with no cast should be used instead.', $env, $this->extensionClass));
                }
            }
        }
        return parent::resolveEnvPlaceholders($value, $format, $usedEnvs);
    }
}
