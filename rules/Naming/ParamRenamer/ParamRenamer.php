<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\ParamRenamer;

use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocManipulator\PropertyDocBlockManipulator;
use RectorPrefix20220606\Rector\Naming\ValueObject\ParamRename;
use RectorPrefix20220606\Rector\Naming\VariableRenamer;
final class ParamRenamer
{
    /**
     * @readonly
     * @var \Rector\Naming\VariableRenamer
     */
    private $variableRenamer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PropertyDocBlockManipulator
     */
    private $propertyDocBlockManipulator;
    public function __construct(VariableRenamer $variableRenamer, PropertyDocBlockManipulator $propertyDocBlockManipulator)
    {
        $this->variableRenamer = $variableRenamer;
        $this->propertyDocBlockManipulator = $propertyDocBlockManipulator;
    }
    public function rename(ParamRename $paramRename) : void
    {
        // 1. rename param
        $paramRename->getVariable()->name = $paramRename->getExpectedName();
        // 2. rename param in the rest of the method
        $this->variableRenamer->renameVariableInFunctionLike($paramRename->getFunctionLike(), $paramRename->getCurrentName(), $paramRename->getExpectedName(), null);
        // 3. rename @param variable in docblock too
        $this->propertyDocBlockManipulator->renameParameterNameInDocBlock($paramRename);
    }
}
