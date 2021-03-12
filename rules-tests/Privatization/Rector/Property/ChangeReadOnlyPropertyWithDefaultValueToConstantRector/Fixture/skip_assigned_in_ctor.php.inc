<?php

namespace Rector\Privatization\Tests\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

use Nette\Utils\Strings;

class SkipAssignedInCtor
{
    private $isAllowed = false;

    public function __construct(string $originalContent)
    {
        $this->isAllowed = (bool) Strings::match($originalContent, '#\d+#');
    }

    public function go()
    {
        return $this->isAllowed;
    }
}
