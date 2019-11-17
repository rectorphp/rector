<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToInjectPropertyRector\Source;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class TestCaseWithGetContainer extends TestCase
{
    public function getContainer(): ContainerInterface
    {
    }
}
