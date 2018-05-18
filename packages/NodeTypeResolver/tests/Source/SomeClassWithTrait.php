<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\Source;

use Doctrine\Common\Persistence\ManagerRegistry;

class SomeClassWithTrait
{
    use ControllerTrait;
}

trait ControllerTrait
{
    public function getDoctrine(): ManagerRegistry
    {
        return new ManagerRegistry();
    }
}