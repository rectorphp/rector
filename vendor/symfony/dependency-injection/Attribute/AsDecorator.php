<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202304\Symfony\Component\DependencyInjection\Attribute;

use RectorPrefix202304\Symfony\Component\DependencyInjection\ContainerInterface;
#[\Attribute(\Attribute::TARGET_CLASS)]
class AsDecorator
{
    /**
     * @var string
     */
    public $decorates;
    /**
     * @var int
     */
    public $priority = 0;
    /**
     * @var int
     */
    public $onInvalid = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
    public function __construct(string $decorates, int $priority = 0, int $onInvalid = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        $this->decorates = $decorates;
        $this->priority = $priority;
        $this->onInvalid = $onInvalid;
    }
}
