<?php

declare (strict_types=1);
namespace Rector\Naming\ParamRenamer;

use PhpParser\Node\Param;
use Rector\BetterPhpDocParser\PhpDocManipulator\PropertyDocBlockManipulator;
use Rector\Naming\ValueObject\ParamRename;
use Rector\Naming\VariableRenamer;
final class ParamRenamer
{
    /**
     * @var \Rector\Naming\VariableRenamer
     */
    private $variableRenamer;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PropertyDocBlockManipulator
     */
    private $propertyDocBlockManipulator;
    public function __construct(\Rector\Naming\VariableRenamer $variableRenamer, \Rector\BetterPhpDocParser\PhpDocManipulator\PropertyDocBlockManipulator $propertyDocBlockManipulator)
    {
        $this->variableRenamer = $variableRenamer;
        $this->propertyDocBlockManipulator = $propertyDocBlockManipulator;
    }
    public function rename(\Rector\Naming\ValueObject\ParamRename $paramRename) : void
    {
        // 1. rename param
        $paramRename->getVariable()->name = $paramRename->getExpectedName();
        // 2. rename param in the rest of the method
        $this->variableRenamer->renameVariableInFunctionLike($paramRename->getFunctionLike(), $paramRename->getCurrentName(), $paramRename->getExpectedName(), null);
        // 3. rename @param variable in docblock too
        $this->propertyDocBlockManipulator->renameParameterNameInDocBlock($paramRename);
    }
}
