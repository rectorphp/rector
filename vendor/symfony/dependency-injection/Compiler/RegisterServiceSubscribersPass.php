<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler;

use RectorPrefix20220501\Psr\Container\ContainerInterface as PsrContainerInterface;
use RectorPrefix20220501\Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\Argument\BoundArgument;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\Reference;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\TypedReference;
use RectorPrefix20220501\Symfony\Component\HttpFoundation\Session\SessionInterface;
use RectorPrefix20220501\Symfony\Contracts\Service\ServiceProviderInterface;
use RectorPrefix20220501\Symfony\Contracts\Service\ServiceSubscriberInterface;
/**
 * Compiler pass to register tagged services that require a service locator.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class RegisterServiceSubscribersPass extends \RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass
{
    /**
     * @param mixed $value
     * @return mixed
     */
    protected function processValue($value, bool $isRoot = \false)
    {
        if (!$value instanceof \RectorPrefix20220501\Symfony\Component\DependencyInjection\Definition || $value->isAbstract() || $value->isSynthetic() || !$value->hasTag('container.service_subscriber')) {
            return parent::processValue($value, $isRoot);
        }
        $serviceMap = [];
        $autowire = $value->isAutowired();
        foreach ($value->getTag('container.service_subscriber') as $attributes) {
            if (!$attributes) {
                $autowire = \true;
                continue;
            }
            \ksort($attributes);
            if ([] !== \array_diff(\array_keys($attributes), ['id', 'key'])) {
                throw new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('The "container.service_subscriber" tag accepts only the "key" and "id" attributes, "%s" given for service "%s".', \implode('", "', \array_keys($attributes)), $this->currentId));
            }
            if (!\array_key_exists('id', $attributes)) {
                throw new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Missing "id" attribute on "container.service_subscriber" tag with key="%s" for service "%s".', $attributes['key'], $this->currentId));
            }
            if (!\array_key_exists('key', $attributes)) {
                $attributes['key'] = $attributes['id'];
            }
            if (isset($serviceMap[$attributes['key']])) {
                continue;
            }
            $serviceMap[$attributes['key']] = new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Reference($attributes['id']);
        }
        $class = $value->getClass();
        if (!($r = $this->container->getReflectionClass($class))) {
            throw new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $class, $this->currentId));
        }
        if (!$r->isSubclassOf(\RectorPrefix20220501\Symfony\Contracts\Service\ServiceSubscriberInterface::class)) {
            throw new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Service "%s" must implement interface "%s".', $this->currentId, \RectorPrefix20220501\Symfony\Contracts\Service\ServiceSubscriberInterface::class));
        }
        $class = $r->name;
        // to remove when symfony/dependency-injection will stop being compatible with symfony/framework-bundle<6.0
        $replaceDeprecatedSession = $this->container->has('.session.deprecated') && $r->isSubclassOf(\RectorPrefix20220501\Symfony\Bundle\FrameworkBundle\Controller\AbstractController::class);
        $subscriberMap = [];
        foreach ($class::getSubscribedServices() as $key => $type) {
            if (!\is_string($type) || !\preg_match('/(?(DEFINE)(?<cn>[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*+))(?(DEFINE)(?<fqcn>(?&cn)(?:\\\\(?&cn))*+))^\\??(?&fqcn)(?:(?:\\|(?&fqcn))*+|(?:&(?&fqcn))*+)$/', $type)) {
                throw new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('"%s::getSubscribedServices()" must return valid PHP types for service "%s" key "%s", "%s" returned.', $class, $this->currentId, $key, \is_string($type) ? $type : \get_debug_type($type)));
            }
            if ($optionalBehavior = '?' === $type[0]) {
                $type = \substr($type, 1);
                $optionalBehavior = \RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
            }
            if (\is_int($name = $key)) {
                $key = $type;
                $name = null;
            }
            if (!isset($serviceMap[$key])) {
                if (!$autowire) {
                    throw new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Service "%s" misses a "container.service_subscriber" tag with "key"/"id" attributes corresponding to entry "%s" as returned by "%s::getSubscribedServices()".', $this->currentId, $key, $class));
                }
                if ($replaceDeprecatedSession && \RectorPrefix20220501\Symfony\Component\HttpFoundation\Session\SessionInterface::class === $type) {
                    // This prevents triggering the deprecation when building the container
                    // to remove when symfony/dependency-injection will stop being compatible with symfony/framework-bundle<6.0
                    $type = '.session.deprecated';
                }
                $serviceMap[$key] = new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Reference($type);
            }
            if ($name) {
                if (\false !== ($i = \strpos($name, '::get'))) {
                    $name = \lcfirst(\substr($name, 5 + $i));
                } elseif (\strpos($name, '::') !== \false) {
                    $name = null;
                }
            }
            if (null !== $name && !$this->container->has($name) && !$this->container->has($type . ' $' . $name)) {
                $camelCaseName = \lcfirst(\str_replace(' ', '', \ucwords(\preg_replace('/[^a-zA-Z0-9\\x7f-\\xff]++/', ' ', $name))));
                $name = $this->container->has($type . ' $' . $camelCaseName) ? $camelCaseName : $name;
            }
            $subscriberMap[$key] = new \RectorPrefix20220501\Symfony\Component\DependencyInjection\TypedReference((string) $serviceMap[$key], $type, $optionalBehavior ?: \RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $name);
            unset($serviceMap[$key]);
        }
        if ($serviceMap = \array_keys($serviceMap)) {
            $message = \sprintf(1 < \count($serviceMap) ? 'keys "%s" do' : 'key "%s" does', \str_replace('%', '%%', \implode('", "', $serviceMap)));
            throw new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Service %s not exist in the map returned by "%s::getSubscribedServices()" for service "%s".', $message, $class, $this->currentId));
        }
        $locatorRef = \RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass::register($this->container, $subscriberMap, $this->currentId);
        $value->addTag('container.service_subscriber.locator', ['id' => (string) $locatorRef]);
        $value->setBindings([\RectorPrefix20220501\Psr\Container\ContainerInterface::class => new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Argument\BoundArgument($locatorRef, \false), \RectorPrefix20220501\Symfony\Contracts\Service\ServiceProviderInterface::class => new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Argument\BoundArgument($locatorRef, \false)] + $value->getBindings());
        return parent::processValue($value);
    }
}
