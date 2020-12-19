<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\Native\VarTag\ArrayType;

final class ArrayWithTypedComma
{
    /**
     * @var array{
     *      type:string,
     * }
     */
    private $mappings = [];
}
