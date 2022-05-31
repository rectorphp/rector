<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220531\Symfony\Component\DependencyInjection\Attribute;

/**
 * An attribute to tell under which index and priority a service class should be found in tagged iterators/locators.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class AsTaggedItem
{
    /**
     * @var string|null
     */
    public $index;
    /**
     * @var int|null
     */
    public $priority;
    public function __construct(?string $index = null, ?int $priority = null)
    {
        $this->index = $index;
        $this->priority = $priority;
    }
}
