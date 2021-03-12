<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source;

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
