<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source;

class ClassWithPhpDocProps
{
    public $implicitMixed;

    /** @var mixed */
    public $explicitMixed;

    /** @var string */
    public $text;

    /** @var int */
    public $number;

    /** @var string|null */
    public $textNullable;

    /** @var ?int */
    public $numberNullable;

    /** @var Abc */
    public $abc;

    /** @var ?Abc */
    public $abcNullable;

    /** @var \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\Abc */
    public $abcFQ;

    /** @var IDontExist */
    public $nonexistent;

    /** @var \A\B\C\IDontExist */
    public $nonexistentFQ;

    /** @var array */
    public $array;

    /** @var array<Abc> */
    public $arrayOfAbcs;
}
