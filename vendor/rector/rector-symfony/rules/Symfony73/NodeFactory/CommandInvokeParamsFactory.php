<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\NodeFactory;

use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\Symfony\Symfony73\ValueObject\CommandArgument;
use Rector\Symfony\Symfony73\ValueObject\CommandOption;
final class CommandInvokeParamsFactory
{
    /**
     * @param CommandArgument[] $commandArguments
     * @param CommandOption[] $commandOptions
     * @return Param[]
     */
    public function createParams(array $commandArguments, array $commandOptions) : array
    {
        $argumentParams = $this->createArgumentParams($commandArguments);
        $optionParams = $this->createOptionParams($commandOptions);
        return \array_merge($argumentParams, $optionParams);
    }
    /**
     * @param CommandArgument[] $commandArguments
     * @return Param[]
     */
    private function createArgumentParams(array $commandArguments) : array
    {
        $argumentParams = [];
        foreach ($commandArguments as $commandArgument) {
            $argumentParam = new Param(new Variable($commandArgument->getName()));
            $argumentParam->type = new Identifier('string');
            // @todo fill type or default value
            // @todo default string, multiple values array
            $argumentParam->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(SymfonyAttribute::COMMAND_ARGUMENT))]);
            $argumentParams[] = $argumentParam;
        }
        return $argumentParams;
    }
    /**
     * @param CommandOption[] $commandOptions
     * @return Param[]
     */
    private function createOptionParams(array $commandOptions) : array
    {
        $optionParams = [];
        foreach ($commandOptions as $commandOption) {
            $optionParam = new Param(new Variable($commandOption->getName()));
            // @todo fill type or default value
            $optionParam->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(SymfonyAttribute::COMMAND_OPTION))]);
            $optionParams[] = $optionParam;
        }
        return $optionParams;
    }
}
