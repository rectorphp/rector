<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Source;

trait MethodSomeTrait
{
    abstract protected function configureRoutes();

    public function run()
    {
        $this->configureRoutes();
    }
}
