<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\NodeAnalyzer;

use PhpParser\Node\Expr\Array_;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Ssch\TYPO3Rector\NodeFactory\CommandArrayItemFactory;
final class CommandArrayDecorator
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\NodeFactory\CommandArrayItemFactory
     */
    private $commandArrayItemFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(\Ssch\TYPO3Rector\NodeFactory\CommandArrayItemFactory $commandArrayItemFactory, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->commandArrayItemFactory = $commandArrayItemFactory;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @param array<string, mixed> $commands
     */
    public function decorateArray(\PhpParser\Node\Expr\Array_ $array, array $commands) : void
    {
        $existingCommands = $this->valueResolver->getValue($array) ?? [];
        $commands = \array_filter($commands, function (string $command) use($existingCommands) {
            return \array_reduce($existingCommands, function ($carry, $existingCommand) use($command) {
                return $existingCommand['class'] !== $command && $carry;
            }, \true);
        });
        $arrayItems = $this->commandArrayItemFactory->createArrayItems($commands);
        $array->items = \array_merge($array->items, $arrayItems);
    }
}
