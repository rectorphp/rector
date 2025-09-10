<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\CommandMethodName;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\Symfony\Symfony73\NodeAnalyzer\CommandArgumentsResolver;
use Rector\Symfony\Symfony73\NodeAnalyzer\CommandOptionsResolver;
use Rector\Symfony\Symfony73\NodeFactory\CommandInvokeParamsFactory;
use Rector\Symfony\Symfony73\NodeTransformer\CommandUnusedInputOutputRemover;
use Rector\Symfony\Symfony73\NodeTransformer\ConsoleOptionAndArgumentMethodCallVariableReplacer;
use Rector\Symfony\Symfony73\NodeTransformer\OutputInputSymfonyStyleReplacer;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/blog/new-in-symfony-7-3-invokable-commands-and-input-attributes
 *
 * @see https://github.com/symfony/symfony-docs/issues/20553
 * @see https://github.com/symfony/symfony/pull/59340
 *
 * @see \Rector\Symfony\Tests\Symfony73\Rector\Class_\InvokableCommandInputAttributeRector\InvokableCommandInputAttributeRectorTest
 */
final class InvokableCommandInputAttributeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private AttributeFinder $attributeFinder;
    /**
     * @readonly
     */
    private CommandArgumentsResolver $commandArgumentsResolver;
    /**
     * @readonly
     */
    private CommandOptionsResolver $commandOptionsResolver;
    /**
     * @readonly
     */
    private CommandInvokeParamsFactory $commandInvokeParamsFactory;
    /**
     * @readonly
     */
    private ConsoleOptionAndArgumentMethodCallVariableReplacer $consoleOptionAndArgumentMethodCallVariableReplacer;
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    /**
     * @readonly
     */
    private OutputInputSymfonyStyleReplacer $outputInputSymfonyStyleReplacer;
    /**
     * @readonly
     */
    private CommandUnusedInputOutputRemover $commandUnusedInputOutputRemover;
    private const MIGRATED_CONFIGURE_CALLS = ['addArgument', 'addOption'];
    public function __construct(AttributeFinder $attributeFinder, CommandArgumentsResolver $commandArgumentsResolver, CommandOptionsResolver $commandOptionsResolver, CommandInvokeParamsFactory $commandInvokeParamsFactory, ConsoleOptionAndArgumentMethodCallVariableReplacer $consoleOptionAndArgumentMethodCallVariableReplacer, VisibilityManipulator $visibilityManipulator, OutputInputSymfonyStyleReplacer $outputInputSymfonyStyleReplacer, CommandUnusedInputOutputRemover $commandUnusedInputOutputRemover)
    {
        $this->attributeFinder = $attributeFinder;
        $this->commandArgumentsResolver = $commandArgumentsResolver;
        $this->commandOptionsResolver = $commandOptionsResolver;
        $this->commandInvokeParamsFactory = $commandInvokeParamsFactory;
        $this->consoleOptionAndArgumentMethodCallVariableReplacer = $consoleOptionAndArgumentMethodCallVariableReplacer;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->outputInputSymfonyStyleReplacer = $outputInputSymfonyStyleReplacer;
        $this->commandUnusedInputOutputRemover = $commandUnusedInputOutputRemover;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change Symfony Command with execute() + configure() to __invoke() with attributes', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'some_name')]
final class SomeCommand extends Command
{
    public function configure()
    {
        $this->addArgument('argument', InputArgument::REQUIRED, 'Argument description');
        $this->addOption('option', 'o', InputOption::VALUE_NONE, 'Option description');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $someArgument = $input->getArgument('argument');
        $someOption = $input->getOption('option');

        // ...

        return Command::SUCCESS;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Argument;
use Symfony\Component\Console\Option;

#[AsCommand(name: 'some_name')]
final class SomeCommand
{
    public function __invoke(
        #[Argument(name: 'argument', description: 'Argument description')]
        string $argument,
        #[Option(name: 'option', shortcut: 'o', mode: Option::VALUE_NONE, description: 'Option description')]
        bool $option = false,
    ) {
        $someArgument = $argument;
        $someOption = $option;

        // ...

        return Command::SUCCESS;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        if (!$node->extends instanceof Name) {
            return null;
        }
        // handle only direct child classes, to keep safe
        if (!$this->isName($node->extends, SymfonyClass::COMMAND)) {
            return null;
        }
        if ($this->isComplexCommand($node)) {
            return null;
        }
        // as command attribute is required, its handled by previous symfony versions
        if (!$this->attributeFinder->hasAttributeByClasses($node, [SymfonyAttribute::AS_COMMAND])) {
            return null;
        }
        $executeClassMethod = $node->getMethod(CommandMethodName::EXECUTE);
        if (!$executeClassMethod instanceof ClassMethod) {
            return null;
        }
        // 1. rename execute to __invoke
        $executeClassMethod->name = new Identifier(MethodName::INVOKE);
        $this->visibilityManipulator->makePublic($executeClassMethod);
        // 2. fetch configure method to get arguments and options metadata
        $configureClassMethod = $node->getMethod(CommandMethodName::CONFIGURE);
        if ($configureClassMethod instanceof ClassMethod) {
            // 3. create arguments and options parameters
            $commandArguments = $this->commandArgumentsResolver->resolve($configureClassMethod);
            $commandOptions = $this->commandOptionsResolver->resolve($configureClassMethod);
            // 4. remove configure() method
            $this->removeConfigureClassMethodIfNotUseful($node);
            // 5. decorate __invoke method with attributes
            $invokeParams = $this->commandInvokeParamsFactory->createParams($commandArguments, $commandOptions);
        } else {
            $invokeParams = [];
        }
        $executeClassMethod->params = array_merge($invokeParams, [$executeClassMethod->params[1]]);
        // 6. remove parent class
        $node->extends = null;
        $this->removeOverrideAttributeAsDifferentMethod($executeClassMethod);
        if ($configureClassMethod instanceof ClassMethod) {
            // 7. replace input->getArgument() and input->getOption() calls with direct variable access
            $this->consoleOptionAndArgumentMethodCallVariableReplacer->replace($executeClassMethod);
        }
        $this->outputInputSymfonyStyleReplacer->replace($executeClassMethod);
        $this->commandUnusedInputOutputRemover->remove($executeClassMethod);
        return $node;
    }
    /**
     * Skip commands with interact() or initialize() methods as modify the argument/option values
     */
    private function isComplexCommand(Class_ $class): bool
    {
        if ($class->getMethod(CommandMethodName::INTERACT) instanceof ClassMethod) {
            return \true;
        }
        return $class->getMethod(CommandMethodName::INITIALIZE) instanceof ClassMethod;
    }
    private function removeConfigureClassMethodIfNotUseful(Class_ $class): void
    {
        foreach ($class->stmts as $key => $stmt) {
            if (!$stmt instanceof ClassMethod) {
                continue;
            }
            if (!$this->isName($stmt->name, CommandMethodName::CONFIGURE)) {
                continue;
            }
            foreach ((array) $stmt->stmts as $innerKey => $innerStmt) {
                if (!$innerStmt instanceof Expression) {
                    continue;
                }
                $expr = $innerStmt->expr;
                if (!$expr instanceof MethodCall) {
                    continue;
                }
                if ($this->isFluentArgumentOptionChain($expr)) {
                    unset($stmt->stmts[$innerKey]);
                    continue;
                }
                if ($this->isName($expr->var, 'this') && $this->isNames($expr->name, self::MIGRATED_CONFIGURE_CALLS)) {
                    unset($stmt->stmts[$innerKey]);
                }
            }
            // 2. if configure() has became empty → remove the method itself
            if ($stmt->stmts === [] || $stmt->stmts === null) {
                unset($class->stmts[$key]);
            }
            return;
        }
    }
    private function isFluentArgumentOptionChain(MethodCall $methodCall): bool
    {
        $current = $methodCall;
        while ($current instanceof MethodCall) {
            // every link must be addArgument() or addOption()
            if (!$this->isNames($current->name, self::MIGRATED_CONFIGURE_CALLS)) {
                return \false;
            }
            // go one step left
            $current = $current->var;
        }
        // the left-most var must be $this
        return $current instanceof Variable && $this->isName($current, 'this');
    }
    private function removeOverrideAttributeAsDifferentMethod(ClassMethod $executeClassMethod): void
    {
        foreach ($executeClassMethod->attrGroups as $attrGroupKey => $attrGroup) {
            foreach ($attrGroup->attrs as $attributeKey => $attr) {
                if ($this->isName($attr->name, 'Override')) {
                    unset($attrGroup->attrs[$attributeKey]);
                }
            }
            // is attribute empty? remove whole group
            if ($attrGroup->attrs === []) {
                unset($executeClassMethod->attrGroups[$attrGroupKey]);
            }
        }
    }
}
