<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\MethodCall\MethodCallRemoverRector\Source;

final class CarType
{
    /** @var string */
    private $type;

    public function getType(): string
    {
        return $this->type;
    }
}
