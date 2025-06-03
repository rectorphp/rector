<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\Symfony\Symfony73\ValueObject\CommandArgument;
use Rector\Symfony\Symfony73\ValueObject\CommandOption;
final class CommandInvokeParamsFactory
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
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
            $argumentName = (string) $this->valueResolver->getValue($commandArgument->getName());
            $variableName = \str_replace('-', '_', $argumentName);
            $argumentParam = new Param(new Variable($variableName));
            $argumentParam->type = new Identifier('string');
            $modeValue = $this->valueResolver->getValue($commandArgument->getMode());
            if ($modeValue === null || $modeValue === 2) {
                $argumentParam->type = new NullableType($argumentParam->type);
            }
            // @todo fill type or default value
            // @todo default string, multiple values array
            $argumentParam->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(SymfonyAttribute::COMMAND_ARGUMENT), [new Arg($commandArgument->getName(), \false, \false, [], new Identifier('name')), new Arg($commandArgument->getDescription(), \false, \false, [], new Identifier('description'))])]);
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
            $optionName = $commandOption->getName();
            $variableName = \str_replace('-', '_', $optionName);
            $optionParam = new Param(new Variable($variableName));
            // @todo fill type or default value
            $optionParam->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(SymfonyAttribute::COMMAND_OPTION))]);
            $optionParams[] = $optionParam;
        }
        return $optionParams;
    }
}
