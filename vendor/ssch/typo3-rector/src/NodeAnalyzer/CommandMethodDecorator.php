<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\NodeAnalyzer;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220531\Symfony\Component\Console\Input\InputArgument;
final class CommandMethodDecorator
{
    /**
     * @var array<int, string>
     */
    private const MODE_MAPPING = [\RectorPrefix20220531\Symfony\Component\Console\Input\InputArgument::OPTIONAL => 'OPTIONAL', \RectorPrefix20220531\Symfony\Component\Console\Input\InputArgument::REQUIRED => 'REQUIRED'];
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
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param array<array{mode: int, name: string, description: string, default: mixed}> $commandInputArguments
     */
    public function decorate(\PhpParser\Node\Stmt\ClassMethod $classMethod, array $commandInputArguments) : void
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
    private function addArgumentsToConfigureMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, array $commandInputArguments) : void
    {
        foreach ($commandInputArguments as $commandInputArgument) {
            $mode = $this->createMode($commandInputArgument['mode']);
            $name = new \PhpParser\Node\Scalar\String_($commandInputArgument['name']);
            $description = new \PhpParser\Node\Scalar\String_($commandInputArgument['description']);
            $defaultValue = $commandInputArgument['default'];
            $classMethod->stmts[] = new \PhpParser\Node\Stmt\Expression($this->nodeFactory->createMethodCall('this', 'addArgument', [$name, $mode, $description, $defaultValue]));
        }
    }
    /**
     * @param array<array{mode: int, name: string, description: string, default: mixed}> $commandInputArguments
     */
    private function addArgumentsToExecuteMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, array $commandInputArguments) : void
    {
        if (null === $classMethod->stmts) {
            return;
        }
        $argumentStatements = [];
        foreach ($commandInputArguments as $commandInputArgument) {
            $name = $commandInputArgument['name'];
            $variable = new \PhpParser\Node\Expr\Variable($name);
            $inputMethodCall = $this->nodeFactory->createMethodCall('input', 'getArgument', [$name]);
            $assignment = new \PhpParser\Node\Expr\Assign($variable, $inputMethodCall);
            $argumentStatements[] = new \PhpParser\Node\Stmt\Expression($assignment);
        }
        \array_unshift($classMethod->stmts, ...$argumentStatements);
    }
    private function createMode(int $mode) : \PhpParser\Node\Expr\ClassConstFetch
    {
        return $this->nodeFactory->createClassConstFetch('Symfony\\Component\\Console\\Input\\InputArgument', self::MODE_MAPPING[$mode]);
    }
}
