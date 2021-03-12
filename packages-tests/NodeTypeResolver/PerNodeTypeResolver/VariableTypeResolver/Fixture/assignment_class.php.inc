<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver\Source\AnotherType;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver\Source\FirstType;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver\Source\SecondType;

final class AssignmentClass
{
    /**
     * @var FirstType
     */
    private $property;

    public function getValue(): SecondType
    {
        $variable = new AnotherType();
        $assignedVariable = $variable;
    }
}

$someClass = new NewClass;
