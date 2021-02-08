<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source;

final class ClassWithTypedPropertyTypes
{
    public $implicitMixed;

    public string $text;

    public int $number;

    public ?string $textNullable;

    public ?int $numberNullable;

    public Abc $abc;

    public ?Abc $abcNullable;

    public \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\Abc $abcFQ;

    public IDontExist $nonexistent;

    public \A\B\C\IDontExist $nonexistentFQ;

    public array $array;

    /** @var array<Abc> */
    public array $arrayOfAbcs;
}
