<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;

final class NewToStaticCall
{
    public function __construct(
        private readonly string $type,
        private readonly string $staticCallClass,
        private readonly string $staticCallMethod
    ) {
        RectorAssert::className($type);
        RectorAssert::className($staticCallClass);
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->type);
    }

    public function getStaticCallClass(): string
    {
        return $this->staticCallClass;
    }

    public function getStaticCallMethod(): string
    {
        return $this->staticCallMethod;
    }
}
