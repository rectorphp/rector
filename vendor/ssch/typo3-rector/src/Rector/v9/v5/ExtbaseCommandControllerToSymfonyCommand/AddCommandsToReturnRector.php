<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/m/typo3/reference-coreapi/9.5/en-us/ApiOverview/CommandControllers/Index.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommandRector\ExtbaseCommandControllerToSymfonyCommandRectorTest
 */
final class AddCommandsToReturnRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const COMMANDS = 'commands';
    /**
     * @var string[]
     */
    private $commands = [];
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add arguments to configure method in Symfony Command', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
protected function configure(): void
{
        $this->setDescription('This is the description of the command');
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
protected function configure(): void
{
        $this->setDescription('This is the description of the command');
        $this->addArgument('foo', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'The foo argument', null);
}
CODE_SAMPLE
, [self::COMMANDS => ['Command' => 'Command']])]);
    }
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Return_::class];
    }
    /**
     * @param Return_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ([] === $this->commands) {
            return null;
        }
        if (!$node->expr instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $existingCommands = $this->valueResolver->getValue($node->expr) ?? [];
        $commands = \array_filter($this->commands, function (string $command) use($existingCommands) {
            return \array_reduce($existingCommands, function ($carry, $existingCommand) use($command) {
                return $existingCommand['class'] !== $command && $carry;
            }, \true);
        });
        foreach ($commands as $commandName => $command) {
            $commandArray = new \PhpParser\Node\Expr\Array_();
            $commandArray->items[] = new \PhpParser\Node\Expr\ArrayItem($this->nodeFactory->createClassConstReference($command), new \PhpParser\Node\Scalar\String_('class'), \false, [\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS => [new \PhpParser\Comment(\PHP_EOL)]]);
            $node->expr->items[] = new \PhpParser\Node\Expr\ArrayItem($commandArray, new \PhpParser\Node\Scalar\String_($commandName), \false, [\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS => [new \PhpParser\Comment(\PHP_EOL)]]);
        }
        return $node;
    }
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration) : void
    {
        $commandInputArguments = $configuration[self::COMMANDS] ?? [];
        $this->commands = $commandInputArguments;
    }
}
