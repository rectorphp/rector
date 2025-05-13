<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Symfony\Symfony73\ValueObject\CommandArgument;
use Rector\Symfony\Symfony73\ValueObject\CommandOption;
final class CommandArgumentsAndOptionsResolver
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
     * @return CommandArgument[]
     */
    public function collectCommandArguments(ClassMethod $configureClassMethod) : array
    {
        $addArgumentMethodCalls = $this->findMethodCallsByName($configureClassMethod, 'addArgument');
        $commandArguments = [];
        foreach ($addArgumentMethodCalls as $addArgumentMethodCall) {
            // @todo extract name, type and requirements
            $addArgumentArgs = $addArgumentMethodCall->getArgs();
            $optionName = $this->valueResolver->getValue($addArgumentArgs[0]->value);
            if ($optionName === null) {
                // we need string value, otherwise param will not have a name
                throw new ShouldNotHappenException('Argument name is required');
            }
            $mode = isset($addArgumentArgs[1]) ? $this->valueResolver->getValue($addArgumentArgs[1]->value) : null;
            if ($mode !== null && !\is_numeric($mode)) {
                // we need numeric value or null, otherwise param will not have a name
                throw new ShouldNotHappenException('Argument mode is required to be null or numeric');
            }
            $description = isset($addArgumentArgs[2]) ? $this->valueResolver->getValue($addArgumentArgs[2]->value) : null;
            if (!\is_string($description)) {
                // we need string value, otherwise param will not have a name
                throw new ShouldNotHappenException('Argument description is required');
            }
            $commandArguments[] = new CommandArgument($addArgumentArgs[0]->value, $addArgumentArgs[1]->value, $addArgumentArgs[2]->value);
        }
        return $commandArguments;
    }
    /**
     * @return CommandOption[]
     */
    public function collectCommandOptions(ClassMethod $configureClassMethod) : array
    {
        $addOptionMethodCalls = $this->findMethodCallsByName($configureClassMethod, 'addOption');
        $commandOptionMetadatas = [];
        foreach ($addOptionMethodCalls as $addOptionMethodCall) {
            // @todo extract name, type and requirements
            $addOptionArgs = $addOptionMethodCall->getArgs();
            $nameArgValue = $addOptionArgs[0]->value;
            if (!$nameArgValue instanceof String_) {
                // we need string value, otherwise param will not have a name
                throw new ShouldNotHappenException('Option name is required');
            }
            $optionName = $nameArgValue->value;
            $commandOptionMetadatas[] = new CommandOption($optionName);
        }
        return $commandOptionMetadatas;
    }
    /**
     * @return MethodCall[]
     */
    private function findMethodCallsByName(ClassMethod $classMethod, string $desiredMethodName) : array
    {
        $nodeFinder = new NodeFinder();
        return $nodeFinder->find($classMethod, function (Node $node) use($desiredMethodName) : bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            if (!$node->name instanceof Identifier) {
                return \false;
            }
            return $node->name->toString() === $desiredMethodName;
        });
    }
}
