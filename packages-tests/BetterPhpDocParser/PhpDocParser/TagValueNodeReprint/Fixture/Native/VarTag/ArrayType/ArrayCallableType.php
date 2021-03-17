<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\Native\VarTag\ArrayType;

final class ArrayCallableType
{
    /**
     * @var (callable(mixed $value, string $typeName):mixed)[]
     */
    private $mappings = [];
}
