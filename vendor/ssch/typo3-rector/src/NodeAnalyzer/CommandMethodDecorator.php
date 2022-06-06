<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Symfony\Component\Console\Input\InputArgument;
final class CommandMethodDecorator
{
    /**
     * @var array<int, string>
     */
    private const MODE_MAPPING = [InputArgument::OPTIONAL => 'OPTIONAL', InputArgument::REQUIRED => 'REQUIRED'];
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param array<array{mode: int, name: string, description: string, default: mixed}> $commandInputArguments
     */
    public function decorate(ClassMethod $classMethod, array $commandInputArguments) : void
    {
        if ([] === $commandInputArguments) {
            return;
        }
        if ($this->nodeNameResolver->isName($classMethod->name, 'configure')) {
            $this->addArgumentsToConfigureMethod($classMethod, $commandInputArguments);
            return;
        }
        if ($this->nodeNameResolver->isName($classMethod->name, 'execute')) {
            $this->addArgumentsToExecuteMethod($classMethod, $commandInputArguments);
        }
    }
    /**
     * @param array<array{mode: int, name: string, description: string, default: mixed}> $commandInputArguments
     */
    private function addArgumentsToConfigureMethod(ClassMethod $classMethod, array $commandInputArguments) : void
    {
        foreach ($commandInputArguments as $commandInputArgument) {
            $mode = $this->createMode($commandInputArgument['mode']);
            $name = new String_($commandInputArgument['name']);
            $description = new String_($commandInputArgument['description']);
            $defaultValue = $commandInputArgument['default'];
            $classMethod->stmts[] = new Expression($this->nodeFactory->createMethodCall('this', 'addArgument', [$name, $mode, $description, $defaultValue]));
        }
    }
    /**
     * @param array<array{mode: int, name: string, description: string, default: mixed}> $commandInputArguments
     */
    private function addArgumentsToExecuteMethod(ClassMethod $classMethod, array $commandInputArguments) : void
    {
        if (null === $classMethod->stmts) {
            return;
        }
        $argumentStatements = [];
        foreach ($commandInputArguments as $commandInputArgument) {
            $name = $commandInputArgument['name'];
            $variable = new Variable($name);
            $inputMethodCall = $this->nodeFactory->createMethodCall('input', 'getArgument', [$name]);
            $assignment = new Assign($variable, $inputMethodCall);
            $argumentStatements[] = new Expression($assignment);
        }
        \array_unshift($classMethod->stmts, ...$argumentStatements);
    }
    private function createMode(int $mode) : ClassConstFetch
    {
        return $this->nodeFactory->createClassConstFetch('Symfony\\Component\\Console\\Input\\InputArgument', self::MODE_MAPPING[$mode]);
    }
}
