<?php

declare(strict_types=1);

namespace Rector\Naming\ParamRenamer;

use PhpParser\Node;
use PhpParser\Node\Param;
use Rector\BetterPhpDocParser\PhpDocManipulator\PropertyDocBlockManipulator;
use Rector\Naming\Contract\RenamerInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\ValueObject\ParamRename;
use Rector\Naming\VariableRenamer;

final class ParamRenamer implements RenamerInterface
{
    /**
     * @var VariableRenamer
     */
    private $variableRenamer;

    /**
     * @var PropertyDocBlockManipulator
     */
    private $propertyDocBlockManipulator;

    public function __construct(
        VariableRenamer $variableRenamer,
        PropertyDocBlockManipulator $propertyDocBlockManipulator
    ) {
        $this->variableRenamer = $variableRenamer;
        $this->propertyDocBlockManipulator = $propertyDocBlockManipulator;
    }

    /**
     * @param ParamRename $renameValueObject
     * @return Param
     */
    public function rename(RenameValueObjectInterface $renameValueObject): ?Node
    {
        // 1. rename param
        $renameValueObject->getVariable()
            ->name = $renameValueObject->getExpectedName();

        // 2. rename param in the rest of the method
        $this->variableRenamer->renameVariableInFunctionLike(
            $renameValueObject->getFunctionLike(),
            null,
            $renameValueObject->getCurrentName(),
            $renameValueObject->getExpectedName()
        );

        // 3. rename @param variable in docblock too
        $this->propertyDocBlockManipulator->renameParameterNameInDocBlock($renameValueObject);

        return $renameValueObject->getParam();
    }
}
