<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210630\Symfony\Component\DependencyInjection\Attribute;

/**
 * An attribute to tell under which environement this class should be registered as a service.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 * @annotation
 */
class When
{
    /**
     * @var string
     */
    public $env;
    public function __construct(string $env)
    {
        $this->env = $env;
    }
}
