<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220501\Symfony\Component\Console\Tester;

use RectorPrefix20220501\Symfony\Component\Console\Command\Command;
use RectorPrefix20220501\Symfony\Component\Console\Completion\CompletionInput;
use RectorPrefix20220501\Symfony\Component\Console\Completion\CompletionSuggestions;
/**
 * Eases the testing of command completion.
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
class CommandCompletionTester
{
    private $command;
    public function __construct(\RectorPrefix20220501\Symfony\Component\Console\Command\Command $command)
    {
        $this->command = $command;
    }
    /**
     * Create completion suggestions from input tokens.
     */
    public function complete(array $input) : array
    {
        $currentIndex = \count($input);
        if ('' === \end($input)) {
            \array_pop($input);
        }
        \array_unshift($input, $this->command->getName());
        $completionInput = \RectorPrefix20220501\Symfony\Component\Console\Completion\CompletionInput::fromTokens($input, $currentIndex);
        $completionInput->bind($this->command->getDefinition());
        $suggestions = new \RectorPrefix20220501\Symfony\Component\Console\Completion\CompletionSuggestions();
        $this->command->complete($completionInput, $suggestions);
        $options = [];
        foreach ($suggestions->getOptionSuggestions() as $option) {
            $options[] = '--' . $option->getName();
        }
        return \array_map('strval', \array_merge($options, $suggestions->getValueSuggestions()));
    }
}
