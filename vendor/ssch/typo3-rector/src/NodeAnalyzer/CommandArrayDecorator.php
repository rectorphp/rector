<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\Value\ValueResolver;
use RectorPrefix20220606\Ssch\TYPO3Rector\NodeFactory\CommandArrayItemFactory;
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
    public function __construct(CommandArrayItemFactory $commandArrayItemFactory, ValueResolver $valueResolver)
    {
        $this->commandArrayItemFactory = $commandArrayItemFactory;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @param array<string, mixed> $commands
     */
    public function decorateArray(Array_ $array, array $commands) : void
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
