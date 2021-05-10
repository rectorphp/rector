<?php

declare(strict_types=1);

namespace Rector\Naming\ParamRenamer;

use PhpParser\Node\Param;
use Rector\BetterPhpDocParser\PhpDocManipulator\PropertyDocBlockManipulator;
use Rector\Naming\ValueObject\ParamRename;
use Rector\Naming\VariableRenamer;

final class ParamRenamer
{
    public function __construct(
        private VariableRenamer $variableRenamer,
        private PropertyDocBlockManipulator $propertyDocBlockManipulator
    ) {
    }

    public function rename(ParamRename $paramRename): void
    {
        // 1. rename param
        $paramRename->getVariable()
            ->name = $paramRename->getExpectedName();

        // 2. rename param in the rest of the method
        $this->variableRenamer->renameVariableInFunctionLike(
            $paramRename->getFunctionLike(),
            null,
            $paramRename->getCurrentName(),
            $paramRename->getExpectedName()
        );

        // 3. rename @param variable in docblock too
        $this->propertyDocBlockManipulator->renameParameterNameInDocBlock($paramRename);
    }
}
