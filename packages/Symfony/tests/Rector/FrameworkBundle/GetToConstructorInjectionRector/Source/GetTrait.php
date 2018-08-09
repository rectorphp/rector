<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source;

trait GetTrait
{
    /**
     * @return object
     */
    public function get(string $serviceName)
    {
    }
}
