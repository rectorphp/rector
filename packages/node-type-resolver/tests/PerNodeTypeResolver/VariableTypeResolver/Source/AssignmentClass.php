<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Source;

final class AssignmentClass
{
    /**
     * @var FirstType
     */
    private $property;

    public function getValue(): SecondType
    {
        $variable = new AnotherType;
        $assignedVariable = $variable;
    }
}

$someClass = new NewClass;
