<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Fixture;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Source\AnotherType;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Source\FirstType;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Source\SecondType;

final class NewClass
{
    /**
     * @var FirstType
     */
    private $property;

    public function getValue(): SecondType
    {
        $variable = new AnotherType();
        $variable->test();
        $assignedVariable = $variable;
    }
}
