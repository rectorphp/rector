<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

use Exception;

final class SkipConstruct extends Exception
{
    private $setName;

    public function __construct(string $message, string $setName)
    {
        $this->setName = $setName;

        parent::__construct($message);
    }
}
