<?php

declare(strict_types=1);

namespace Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Source;

trait MethodSomeTrait
{
    abstract protected function configureRoutes();

    public function run()
    {
        $this->configureRoutes();
    }
}
