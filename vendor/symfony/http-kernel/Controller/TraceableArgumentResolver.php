<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211030\Symfony\Component\HttpKernel\Controller;

use RectorPrefix20211030\Symfony\Component\HttpFoundation\Request;
use RectorPrefix20211030\Symfony\Component\Stopwatch\Stopwatch;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TraceableArgumentResolver implements \RectorPrefix20211030\Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface
{
    private $resolver;
    private $stopwatch;
    public function __construct(\RectorPrefix20211030\Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface $resolver, \RectorPrefix20211030\Symfony\Component\Stopwatch\Stopwatch $stopwatch)
    {
        $this->resolver = $resolver;
        $this->stopwatch = $stopwatch;
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param callable $controller
     */
    public function getArguments($request, $controller)
    {
        $e = $this->stopwatch->start('controller.get_arguments');
        $ret = $this->resolver->getArguments($request, $controller);
        $e->stop();
        return $ret;
    }
}
