<?php

declare(strict_types=1);

namespace Symfony\Bundle\FrameworkBundle\Test;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

if (class_exists('Symfony\Bundle\FrameworkBundle\Test\KernelTestCase')) {
    return;
}

class KernelTestCase extends TestCase
{
    /**
     * @var ContainerInterface
     */
    protected static $container;
}
