<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220418\Symfony\Component\DependencyInjection\Attribute;

/**
 * An attribute to tell under which environement this class should be registered as a service.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
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
