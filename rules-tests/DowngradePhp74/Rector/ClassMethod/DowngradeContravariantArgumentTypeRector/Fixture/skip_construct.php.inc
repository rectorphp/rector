<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

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
