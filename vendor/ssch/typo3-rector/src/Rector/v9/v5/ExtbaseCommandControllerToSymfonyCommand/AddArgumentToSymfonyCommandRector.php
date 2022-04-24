<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/m/typo3/reference-coreapi/9.5/en-us/ApiOverview/CommandControllers/Index.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommandRector\ExtbaseCommandControllerToSymfonyCommandRectorTest
 */
final class AddArgumentToSymfonyCommandRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const INPUT_ARGUMENTS = 'input-arguments';
    /**
     * @var array<int, string>
     */
    private const MODE_MAPPING = [2 => 'OPTIONAL', 1 => 'REQUIRED'];
    /**
     * @var string
     */
    private const NAME = 'name';
    /**
     * @var array<string, array<string, mixed>>
     */
    private $commandInputArguments = [];
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add arguments to configure and executed method in Symfony Command', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
protected function configure(): void
{
        $this->setDescription('This is the description of the command');
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
protected function configure(): void
{
        $this->setDescription('This is the description of the command');
        $this->addArgument('foo', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'The parameter foo', null);
}
CODE_SAMPLE
, [self::INPUT_ARGUMENTS => ['foo' => [self::NAME => 'foo', 'description' => 'The parameter foo', 'mode' => 1, 'default' => null]]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ([] === $this->commandInputArguments) {
            return null;
        }
        if ($this->isName($node->name, 'configure')) {
            return $this->addArgumentsToConfigureMethod($node);
        }
        if ($this->isName($node->name, 'execute')) {
            return $this->addArgumentsToExecuteMethod($node);
        }
        return null;
    }
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration) : void
    {
        $commandInputArguments = $configuration[self::INPUT_ARGUMENTS] ?? [];
        $this->commandInputArguments = $commandInputArguments;
    }
    private function addArgumentsToConfigureMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : \PhpParser\Node\Stmt\ClassMethod
    {
        foreach ($this->commandInputArguments as $commandInputArgument) {
            $mode = $this->createMode((int) $commandInputArgument['mode']);
            $name = new \PhpParser\Node\Scalar\String_($commandInputArgument[self::NAME]);
            $description = new \PhpParser\Node\Scalar\String_($commandInputArgument['description']);
            $defaultValue = $commandInputArgument['default'];
            $classMethod->stmts[] = new \PhpParser\Node\Stmt\Expression($this->nodeFactory->createMethodCall('this', 'addArgument', [$name, $mode, $description, $defaultValue]));
        }
        return $classMethod;
    }
    private function addArgumentsToExecuteMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : \PhpParser\Node\Stmt\ClassMethod
    {
        if (null === $classMethod->stmts) {
            return $classMethod;
        }
        $argumentStatements = [];
        foreach ($this->commandInputArguments as $commandInputArgument) {
            $name = $commandInputArgument[self::NAME];
            $variable = new \PhpParser\Node\Expr\Variable($name);
            $inputMethodCall = $this->nodeFactory->createMethodCall('input', 'getArgument', [$name]);
            $assignment = new \PhpParser\Node\Expr\Assign($variable, $inputMethodCall);
            $argumentStatements[] = new \PhpParser\Node\Stmt\Expression($assignment);
        }
        \array_unshift($classMethod->stmts, ...$argumentStatements);
        return $classMethod;
    }
    private function createMode(int $mode) : \PhpParser\Node\Expr\ClassConstFetch
    {
        return $this->nodeFactory->createClassConstFetch('Symfony\\Component\\Console\\Input\\InputArgument', self::MODE_MAPPING[$mode]);
    }
}
