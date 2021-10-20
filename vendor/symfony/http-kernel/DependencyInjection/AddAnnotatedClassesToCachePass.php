<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpKernel\DependencyInjection;

use RectorPrefix20211020\Composer\Autoload\ClassLoader;
use RectorPrefix20211020\Symfony\Component\Debug\DebugClassLoader as LegacyDebugClassLoader;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20211020\Symfony\Component\ErrorHandler\DebugClassLoader;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Kernel;
/**
 * Sets the classes to compile in the cache for the container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class AddAnnotatedClassesToCachePass implements \RectorPrefix20211020\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    private $kernel;
    public function __construct(\RectorPrefix20211020\Symfony\Component\HttpKernel\Kernel $kernel)
    {
        $this->kernel = $kernel;
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process($container)
    {
        $annotatedClasses = $this->kernel->getAnnotatedClassesToCompile();
        foreach ($container->getExtensions() as $extension) {
            if ($extension instanceof \RectorPrefix20211020\Symfony\Component\HttpKernel\DependencyInjection\Extension) {
                $annotatedClasses = \array_merge($annotatedClasses, $extension->getAnnotatedClassesToCompile());
            }
        }
        $existingClasses = $this->getClassesInComposerClassMaps();
        $annotatedClasses = $container->getParameterBag()->resolveValue($annotatedClasses);
        $this->kernel->setAnnotatedClassCache($this->expandClasses($annotatedClasses, $existingClasses));
    }
    /**
     * Expands the given class patterns using a list of existing classes.
     *
     * @param array $patterns The class patterns to expand
     * @param array $classes  The existing classes to match against the patterns
     */
    private function expandClasses(array $patterns, array $classes) : array
    {
        $expanded = [];
        // Explicit classes declared in the patterns are returned directly
        foreach ($patterns as $key => $pattern) {
            if (\substr_compare($pattern, '\\', -\strlen('\\')) !== 0 && \strpos($pattern, '*') === \false) {
                unset($patterns[$key]);
                $expanded[] = \ltrim($pattern, '\\');
            }
        }
        // Match patterns with the classes list
        $regexps = $this->patternsToRegexps($patterns);
        foreach ($classes as $class) {
            $class = \ltrim($class, '\\');
            if ($this->matchAnyRegexps($class, $regexps)) {
                $expanded[] = $class;
            }
        }
        return \array_unique($expanded);
    }
    private function getClassesInComposerClassMaps() : array
    {
        $classes = [];
        foreach (\spl_autoload_functions() as $function) {
            if (!\is_array($function)) {
                continue;
            }
            if ($function[0] instanceof \RectorPrefix20211020\Symfony\Component\ErrorHandler\DebugClassLoader || $function[0] instanceof \RectorPrefix20211020\Symfony\Component\Debug\DebugClassLoader) {
                $function = $function[0]->getClassLoader();
            }
            if (\is_array($function) && $function[0] instanceof \RectorPrefix20211020\Composer\Autoload\ClassLoader) {
                $classes += \array_filter($function[0]->getClassMap());
            }
        }
        return \array_keys($classes);
    }
    private function patternsToRegexps(array $patterns) : array
    {
        $regexps = [];
        foreach ($patterns as $pattern) {
            // Escape user input
            $regex = \preg_quote(\ltrim($pattern, '\\'));
            // Wildcards * and **
            $regex = \strtr($regex, ['\\*\\*' => '.*?', '\\*' => '[^\\\\]*?']);
            // If this class does not end by a slash, anchor the end
            if ('\\' !== \substr($regex, -1)) {
                $regex .= '$';
            }
            $regexps[] = '{^\\\\' . $regex . '}';
        }
        return $regexps;
    }
    private function matchAnyRegexps(string $class, array $regexps) : bool
    {
        $isTest = \strpos($class, 'Test') !== \false;
        foreach ($regexps as $regex) {
            if ($isTest && \strpos($regex, 'Test') === \false) {
                continue;
            }
            if (\preg_match($regex, '\\' . $class)) {
                return \true;
            }
        }
        return \false;
    }
}
