<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Tests\Functions\Source;

use PHPStan\Type\Type;

final class WithType
{
    /**
     * @var Type
     */
    private $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function getType(): Type
    {
        return $this->type;
    }
}
