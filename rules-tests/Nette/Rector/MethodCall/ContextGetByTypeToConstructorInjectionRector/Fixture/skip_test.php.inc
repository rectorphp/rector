<?php

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\Source\SomeTypeToInject;

class SkipTest extends TestCase
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    public function testThat()
    {
        $someTypeToInject = $this->context->getByType(SomeTypeToInject::class);
    }
}
