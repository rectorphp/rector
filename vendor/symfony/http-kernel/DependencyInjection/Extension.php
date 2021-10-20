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

use RectorPrefix20211020\Symfony\Component\DependencyInjection\Extension\Extension as BaseExtension;
/**
 * Allow adding classes to the class cache.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Extension extends \RectorPrefix20211020\Symfony\Component\DependencyInjection\Extension\Extension
{
    private $annotatedClasses = [];
    /**
     * Gets the annotated classes to cache.
     *
     * @return array An array of classes
     */
    public function getAnnotatedClassesToCompile()
    {
        return $this->annotatedClasses;
    }
    /**
     * Adds annotated classes to the class cache.
     *
     * @param array $annotatedClasses An array of class patterns
     */
    public function addAnnotatedClassesToCompile($annotatedClasses)
    {
        $this->annotatedClasses = \array_merge($this->annotatedClasses, $annotatedClasses);
    }
}
