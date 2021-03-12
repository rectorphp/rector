<?php

declare(strict_types=1);

namespace Rector\Tests\Symfony\Rector\MethodCall\GetToConstructorInjectionRector\Source;

trait GetTrait
{
    /**
     * @return object
     */
    public function get(string $serviceName)
    {
    }
}
