<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PHPStan\Type\Type;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\Symfony\Symfony73\ValueObject\CommandArgument;
use Rector\Symfony\Symfony73\ValueObject\CommandOption;
final class CommandInvokeParamsFactory
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    public function __construct(ValueResolver $valueResolver, StaticTypeMapper $staticTypeMapper)
    {
        $this->valueResolver = $valueResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    /**
     * @param CommandArgument[] $commandArguments
     * @param CommandOption[] $commandOptions
     * @return Param[]
     */
    public function createParams(array $commandArguments, array $commandOptions): array
    {
        $argumentParams = $this->createArgumentParams($commandArguments);
        $optionParams = $this->createOptionParams($commandOptions);
        return array_merge($argumentParams, $optionParams);
    }
    /**
     * @param CommandArgument[] $commandArguments
     * @return Param[]
     */
    private function createArgumentParams(array $commandArguments): array
    {
        $argumentParams = [];
        foreach ($commandArguments as $commandArgument) {
            $variableName = $this->createCamelCase($commandArgument->getNameValue());
            $argumentParam = new Param(new Variable($variableName));
            $this->decorateParamType($argumentParam, $commandArgument);
            if ($commandArgument->getDefault() instanceof Expr) {
                $argumentParam->default = $commandArgument->getDefault();
            }
            if ($this->isOptionalArgument($commandArgument)) {
                $argumentParam->type = new NullableType($argumentParam->type);
            }
            // @todo default string, multiple values array
            $argumentArgs = [new Arg($commandArgument->getName(), \false, \false, [], new Identifier('name'))];
            if ($this->isNonEmptyExpr($commandArgument->getDescription())) {
                $argumentArgs[] = new Arg($commandArgument->getDescription(), \false, \false, [], new Identifier('description'));
            }
            $argumentParam->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(SymfonyAttribute::COMMAND_ARGUMENT), $argumentArgs)]);
            $argumentParams[] = $argumentParam;
        }
        return $argumentParams;
    }
    /**
     * @param CommandOption[] $commandOptions
     * @return Param[]
     */
    private function createOptionParams(array $commandOptions): array
    {
        $optionParams = [];
        foreach ($commandOptions as $commandOption) {
            $variableName = $this->createCamelCase($commandOption->getNameValue());
            $optionParam = new Param(new Variable($variableName));
            if ($commandOption->getDefault() instanceof Expr) {
                $optionParam->default = $commandOption->getDefault();
            } elseif ($commandOption->isImplicitBoolean()) {
                $optionParam->default = new ConstFetch(new Name('false'));
            }
            $this->decorateParamType($optionParam, $commandOption);
            $optionArgs = [new Arg($commandOption->getName(), \false, \false, [], new Identifier('name'))];
            if ($this->isNonEmptyExpr($commandOption->getShortcut())) {
                $optionArgs[] = new Arg($commandOption->getShortcut(), \false, \false, [], new Identifier('shortcut'));
            }
            if ($this->isNonEmptyExpr($commandOption->getMode())) {
                $optionArgs[] = new Arg($commandOption->getMode(), \false, \false, [], new Identifier('mode'));
            }
            if ($this->isNonEmptyExpr($commandOption->getDescription())) {
                $optionArgs[] = new Arg($commandOption->getDescription(), \false, \false, [], new Identifier('description'));
            }
            $optionParam->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(SymfonyAttribute::COMMAND_OPTION), $optionArgs)]);
            $optionParams[] = $optionParam;
        }
        return $optionParams;
    }
    private function createCamelCase(string $value): string
    {
        // Replace dashes/underscores with spaces
        $value = str_replace(['-', '_'], ' ', strtolower($value));
        // Capitalize each word, then remove spaces
        $value = str_replace(' ', '', ucwords($value));
        // Lowercase first character to make it camelCase
        return lcfirst($value);
    }
    private function isOptionalArgument(CommandArgument $commandArgument): bool
    {
        if (!$commandArgument->getMode() instanceof Expr) {
            return \true;
        }
        return $this->valueResolver->isValue($commandArgument->getMode(), 2);
    }
    private function isNonEmptyExpr(?Expr $expr): bool
    {
        if (!$expr instanceof Expr) {
            return \false;
        }
        if ($this->valueResolver->isNull($expr)) {
            return \false;
        }
        return !$this->valueResolver->isValue($expr, '');
    }
    /**
     * @param \Rector\Symfony\Symfony73\ValueObject\CommandArgument|\Rector\Symfony\Symfony73\ValueObject\CommandOption $commandArgumentOrOption
     */
    private function decorateParamType(Param $argumentParam, $commandArgumentOrOption): void
    {
        if ($commandArgumentOrOption instanceof CommandOption && $commandArgumentOrOption->isImplicitBoolean()) {
            $argumentParam->type = new Identifier('bool');
            return;
        }
        if ($commandArgumentOrOption->isArray()) {
            $argumentParam->type = new Identifier('array');
            return;
        }
        $defaultType = $commandArgumentOrOption->getDefaultType();
        if ($defaultType instanceof Type) {
            $paramType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($defaultType, TypeKind::PARAM);
            if ($paramType instanceof Node) {
                $argumentParam->type = $paramType;
                return;
            }
        }
        // fallback
        if ($commandArgumentOrOption instanceof CommandOption) {
            return;
        }
        $argumentParam->type = new Identifier('string');
    }
}
