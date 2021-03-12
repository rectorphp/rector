<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source;

final class DocBlockProperties
{
    public $implicitMixed;

    /** @var mixed */
    public $explicitMixed;

    /** @var string */
    public $text;

    /** @var ?int */
    public $numberNullable;

    /** @var array<Abc> */
    public $arrayOfAbcs;
}
