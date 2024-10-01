<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202410\Symfony\Component\Console\Input;

use RectorPrefix202410\Symfony\Component\Console\Command\Command;
use RectorPrefix202410\Symfony\Component\Console\Completion\CompletionInput;
use RectorPrefix202410\Symfony\Component\Console\Completion\CompletionSuggestions;
use RectorPrefix202410\Symfony\Component\Console\Completion\Suggestion;
use RectorPrefix202410\Symfony\Component\Console\Exception\InvalidArgumentException;
use RectorPrefix202410\Symfony\Component\Console\Exception\LogicException;
/**
 * Represents a command line argument.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class InputArgument
{
    public const REQUIRED = 1;
    public const OPTIONAL = 2;
    public const IS_ARRAY = 4;
    /**
     * @var string
     */
    private $name;
    /**
     * @var int
     */
    private $mode;
    /**
     * @var mixed[]|bool|float|int|string|null
     */
    private $default;
    /**
     * @var mixed[]|\Closure
     */
    private $suggestedValues;
    /**
     * @var string
     */
    private $description;
    /**
     * @param string                                                                        $name            The argument name
     * @param int|null                                                                      $mode            The argument mode: a bit mask of self::REQUIRED, self::OPTIONAL and self::IS_ARRAY
     * @param string                                                                        $description     A description text
     * @param string|bool|int|float|array|null                                              $default         The default value (for self::OPTIONAL mode only)
     * @param array|\Closure(CompletionInput,CompletionSuggestions):list<string|Suggestion> $suggestedValues The values used for input completion
     *
     * @throws InvalidArgumentException When argument mode is not valid
     */
    public function __construct(string $name, ?int $mode = null, string $description = '', $default = null, $suggestedValues = [])
    {
        if (null === $mode) {
            $mode = self::OPTIONAL;
        } elseif ($mode > 7 || $mode < 1) {
            throw new InvalidArgumentException(\sprintf('Argument mode "%s" is not valid.', $mode));
        }
        $this->name = $name;
        $this->mode = $mode;
        $this->description = $description;
        $this->suggestedValues = $suggestedValues;
        $this->setDefault($default);
    }
    /**
     * Returns the argument name.
     */
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * Returns true if the argument is required.
     *
     * @return bool true if parameter mode is self::REQUIRED, false otherwise
     */
    public function isRequired() : bool
    {
        return self::REQUIRED === (self::REQUIRED & $this->mode);
    }
    /**
     * Returns true if the argument can take multiple values.
     *
     * @return bool true if mode is self::IS_ARRAY, false otherwise
     */
    public function isArray() : bool
    {
        return self::IS_ARRAY === (self::IS_ARRAY & $this->mode);
    }
    /**
     * Sets the default value.
     *
     * @return void
     *
     * @throws LogicException When incorrect default value is given
     * @param string|bool|int|float|mixed[]|null $default
     */
    public function setDefault($default = null)
    {
        if (1 > \func_num_args()) {
            trigger_deprecation('symfony/console', '6.2', 'Calling "%s()" without any arguments is deprecated, pass null explicitly instead.', __METHOD__);
        }
        if ($this->isRequired() && null !== $default) {
            throw new LogicException('Cannot set a default value except for InputArgument::OPTIONAL mode.');
        }
        if ($this->isArray()) {
            if (null === $default) {
                $default = [];
            } elseif (!\is_array($default)) {
                throw new LogicException('A default value for an array argument must be an array.');
            }
        }
        $this->default = $default;
    }
    /**
     * Returns the default value.
     * @return mixed[]|bool|float|int|string|null
     */
    public function getDefault()
    {
        return $this->default;
    }
    public function hasCompletion() : bool
    {
        return [] !== $this->suggestedValues;
    }
    /**
     * Adds suggestions to $suggestions for the current completion input.
     *
     * @see Command::complete()
     */
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions) : void
    {
        $values = $this->suggestedValues;
        if ($values instanceof \Closure && !\is_array($values = $values($input))) {
            throw new LogicException(\sprintf('Closure for argument "%s" must return an array. Got "%s".', $this->name, \get_debug_type($values)));
        }
        if ($values) {
            $suggestions->suggestValues($values);
        }
    }
    /**
     * Returns the description text.
     */
    public function getDescription() : string
    {
        return $this->description;
    }
}
