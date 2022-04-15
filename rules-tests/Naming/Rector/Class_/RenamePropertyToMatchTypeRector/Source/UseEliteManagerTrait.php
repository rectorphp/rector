<?php

declare(strict_types=1);

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source;

trait UseEliteManagerTrait
{
    public function trigger($name)
    {
        $this->eventManager->trigger($name);
    }
}
