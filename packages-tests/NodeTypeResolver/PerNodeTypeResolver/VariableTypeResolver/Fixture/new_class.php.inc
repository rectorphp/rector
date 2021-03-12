<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver\Source\AnotherType;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver\Source\FirstType;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver\Source\SecondType;

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
