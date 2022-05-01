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

use RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\TypedReference;
use RectorPrefix20220501\Symfony\Contracts\Service\Attribute\Required;
/**
 * Looks for definitions with autowiring enabled and registers their corresponding "@required" properties.
 *
 * @author Sebastien Morel (Plopix) <morel.seb@gmail.com>
 * @author Nicolas Grekas <p@tchwork.com>
 */
class AutowireRequiredPropertiesPass extends \RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass
{
    /**
     * {@inheritdoc}
     * @param mixed $value
     * @return mixed
     */
    protected function processValue($value, bool $isRoot = \false)
    {
        $value = parent::processValue($value, $isRoot);
        if (!$value instanceof \RectorPrefix20220501\Symfony\Component\DependencyInjection\Definition || !$value->isAutowired() || $value->isAbstract() || !$value->getClass()) {
            return $value;
        }
        if (!($reflectionClass = $this->container->getReflectionClass($value->getClass(), \false))) {
            return $value;
        }
        $properties = $value->getProperties();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if (!($type = \method_exists($reflectionProperty, 'getType') ? $reflectionProperty->getType() : null) instanceof \ReflectionNamedType) {
                continue;
            }
            if (!(\method_exists($reflectionProperty, 'getAttributes') ? $reflectionProperty->getAttributes(\RectorPrefix20220501\Symfony\Contracts\Service\Attribute\Required::class) : []) && (\false === ($doc = $reflectionProperty->getDocComment()) || \false === \stripos($doc, '@required') || !\preg_match('#(?:^/\\*\\*|\\n\\s*+\\*)\\s*+@required(?:\\s|\\*/$)#i', $doc))) {
                continue;
            }
            if (\array_key_exists($name = $reflectionProperty->getName(), $properties)) {
                continue;
            }
            $type = $type->getName();
            $value->setProperty($name, new \RectorPrefix20220501\Symfony\Component\DependencyInjection\TypedReference($type, $type, \RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $name));
        }
        return $value;
    }
}
