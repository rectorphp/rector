<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\Console\Input;

use RectorPrefix20211020\Symfony\Component\Console\Exception\InvalidArgumentException;
use RectorPrefix20211020\Symfony\Component\Console\Exception\LogicException;
/**
 * A InputDefinition represents a set of valid command line arguments and options.
 *
 * Usage:
 *
 *     $definition = new InputDefinition([
 *         new InputArgument('name', InputArgument::REQUIRED),
 *         new InputOption('foo', 'f', InputOption::VALUE_REQUIRED),
 *     ]);
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class InputDefinition
{
    private $arguments;
    private $requiredCount;
    private $lastArrayArgument;
    private $lastOptionalArgument;
    private $options;
    private $negations;
    private $shortcuts;
    /**
     * @param array $definition An array of InputArgument and InputOption instance
     */
    public function __construct(array $definition = [])
    {
        $this->setDefinition($definition);
    }
    /**
     * Sets the definition of the input.
     * @param mixed[] $definition
     */
    public function setDefinition($definition)
    {
        $arguments = [];
        $options = [];
        foreach ($definition as $item) {
            if ($item instanceof \RectorPrefix20211020\Symfony\Component\Console\Input\InputOption) {
                $options[] = $item;
            } else {
                $arguments[] = $item;
            }
        }
        $this->setArguments($arguments);
        $this->setOptions($options);
    }
    /**
     * Sets the InputArgument objects.
     *
     * @param InputArgument[] $arguments An array of InputArgument objects
     */
    public function setArguments($arguments = [])
    {
        $this->arguments = [];
        $this->requiredCount = 0;
        $this->lastOptionalArgument = null;
        $this->lastArrayArgument = null;
        $this->addArguments($arguments);
    }
    /**
     * Adds an array of InputArgument objects.
     *
     * @param InputArgument[] $arguments An array of InputArgument objects
     */
    public function addArguments($arguments = [])
    {
        if (null !== $arguments) {
            foreach ($arguments as $argument) {
                $this->addArgument($argument);
            }
        }
    }
    /**
     * @throws LogicException When incorrect argument is given
     * @param \Symfony\Component\Console\Input\InputArgument $argument
     */
    public function addArgument($argument)
    {
        if (isset($this->arguments[$argument->getName()])) {
            throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\LogicException(\sprintf('An argument with name "%s" already exists.', $argument->getName()));
        }
        if (null !== $this->lastArrayArgument) {
            throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\LogicException(\sprintf('Cannot add a required argument "%s" after an array argument "%s".', $argument->getName(), $this->lastArrayArgument->getName()));
        }
        if ($argument->isRequired() && null !== $this->lastOptionalArgument) {
            throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\LogicException(\sprintf('Cannot add a required argument "%s" after an optional one "%s".', $argument->getName(), $this->lastOptionalArgument->getName()));
        }
        if ($argument->isArray()) {
            $this->lastArrayArgument = $argument;
        }
        if ($argument->isRequired()) {
            ++$this->requiredCount;
        } else {
            $this->lastOptionalArgument = $argument;
        }
        $this->arguments[$argument->getName()] = $argument;
    }
    /**
     * Returns an InputArgument by name or by position.
     *
     * @param string|int $name The InputArgument name or position
     *
     * @return InputArgument An InputArgument object
     *
     * @throws InvalidArgumentException When argument given doesn't exist
     */
    public function getArgument($name)
    {
        if (!$this->hasArgument($name)) {
            throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\InvalidArgumentException(\sprintf('The "%s" argument does not exist.', $name));
        }
        $arguments = \is_int($name) ? \array_values($this->arguments) : $this->arguments;
        return $arguments[$name];
    }
    /**
     * Returns true if an InputArgument object exists by name or position.
     *
     * @param string|int $name The InputArgument name or position
     *
     * @return bool true if the InputArgument object exists, false otherwise
     */
    public function hasArgument($name)
    {
        $arguments = \is_int($name) ? \array_values($this->arguments) : $this->arguments;
        return isset($arguments[$name]);
    }
    /**
     * Gets the array of InputArgument objects.
     *
     * @return InputArgument[] An array of InputArgument objects
     */
    public function getArguments()
    {
        return $this->arguments;
    }
    /**
     * Returns the number of InputArguments.
     *
     * @return int The number of InputArguments
     */
    public function getArgumentCount()
    {
        return null !== $this->lastArrayArgument ? \PHP_INT_MAX : \count($this->arguments);
    }
    /**
     * Returns the number of required InputArguments.
     *
     * @return int The number of required InputArguments
     */
    public function getArgumentRequiredCount()
    {
        return $this->requiredCount;
    }
    /**
     * @return array<string|bool|int|float|array|null>
     */
    public function getArgumentDefaults()
    {
        $values = [];
        foreach ($this->arguments as $argument) {
            $values[$argument->getName()] = $argument->getDefault();
        }
        return $values;
    }
    /**
     * Sets the InputOption objects.
     *
     * @param InputOption[] $options An array of InputOption objects
     */
    public function setOptions($options = [])
    {
        $this->options = [];
        $this->shortcuts = [];
        $this->negations = [];
        $this->addOptions($options);
    }
    /**
     * Adds an array of InputOption objects.
     *
     * @param InputOption[] $options An array of InputOption objects
     */
    public function addOptions($options = [])
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
    }
    /**
     * @throws LogicException When option given already exist
     * @param \Symfony\Component\Console\Input\InputOption $option
     */
    public function addOption($option)
    {
        if (isset($this->options[$option->getName()]) && !$option->equals($this->options[$option->getName()])) {
            throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\LogicException(\sprintf('An option named "%s" already exists.', $option->getName()));
        }
        if (isset($this->negations[$option->getName()])) {
            throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\LogicException(\sprintf('An option named "%s" already exists.', $option->getName()));
        }
        if ($option->getShortcut()) {
            foreach (\explode('|', $option->getShortcut()) as $shortcut) {
                if (isset($this->shortcuts[$shortcut]) && !$option->equals($this->options[$this->shortcuts[$shortcut]])) {
                    throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\LogicException(\sprintf('An option with shortcut "%s" already exists.', $shortcut));
                }
            }
        }
        $this->options[$option->getName()] = $option;
        if ($option->getShortcut()) {
            foreach (\explode('|', $option->getShortcut()) as $shortcut) {
                $this->shortcuts[$shortcut] = $option->getName();
            }
        }
        if ($option->isNegatable()) {
            $negatedName = 'no-' . $option->getName();
            if (isset($this->options[$negatedName])) {
                throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\LogicException(\sprintf('An option named "%s" already exists.', $negatedName));
            }
            $this->negations[$negatedName] = $option->getName();
        }
    }
    /**
     * Returns an InputOption by name.
     *
     * @return InputOption A InputOption object
     *
     * @throws InvalidArgumentException When option given doesn't exist
     * @param string $name
     */
    public function getOption($name)
    {
        if (!$this->hasOption($name)) {
            throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\InvalidArgumentException(\sprintf('The "--%s" option does not exist.', $name));
        }
        return $this->options[$name];
    }
    /**
     * Returns true if an InputOption object exists by name.
     *
     * This method can't be used to check if the user included the option when
     * executing the command (use getOption() instead).
     *
     * @return bool true if the InputOption object exists, false otherwise
     * @param string $name
     */
    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }
    /**
     * Gets the array of InputOption objects.
     *
     * @return InputOption[] An array of InputOption objects
     */
    public function getOptions()
    {
        return $this->options;
    }
    /**
     * Returns true if an InputOption object exists by shortcut.
     *
     * @return bool true if the InputOption object exists, false otherwise
     * @param string $name
     */
    public function hasShortcut($name)
    {
        return isset($this->shortcuts[$name]);
    }
    /**
     * Returns true if an InputOption object exists by negated name.
     * @param string $name
     */
    public function hasNegation($name) : bool
    {
        return isset($this->negations[$name]);
    }
    /**
     * Gets an InputOption by shortcut.
     *
     * @return InputOption An InputOption object
     * @param string $shortcut
     */
    public function getOptionForShortcut($shortcut)
    {
        return $this->getOption($this->shortcutToName($shortcut));
    }
    /**
     * @return array<string|bool|int|float|array|null>
     */
    public function getOptionDefaults()
    {
        $values = [];
        foreach ($this->options as $option) {
            $values[$option->getName()] = $option->getDefault();
        }
        return $values;
    }
    /**
     * Returns the InputOption name given a shortcut.
     *
     * @throws InvalidArgumentException When option given does not exist
     *
     * @internal
     * @param string $shortcut
     */
    public function shortcutToName($shortcut) : string
    {
        if (!isset($this->shortcuts[$shortcut])) {
            throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\InvalidArgumentException(\sprintf('The "-%s" option does not exist.', $shortcut));
        }
        return $this->shortcuts[$shortcut];
    }
    /**
     * Returns the InputOption name given a negation.
     *
     * @throws InvalidArgumentException When option given does not exist
     *
     * @internal
     * @param string $negation
     */
    public function negationToName($negation) : string
    {
        if (!isset($this->negations[$negation])) {
            throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\InvalidArgumentException(\sprintf('The "--%s" option does not exist.', $negation));
        }
        return $this->negations[$negation];
    }
    /**
     * Gets the synopsis.
     *
     * @return string The synopsis
     * @param bool $short
     */
    public function getSynopsis($short = \false)
    {
        $elements = [];
        if ($short && $this->getOptions()) {
            $elements[] = '[options]';
        } elseif (!$short) {
            foreach ($this->getOptions() as $option) {
                $value = '';
                if ($option->acceptValue()) {
                    $value = \sprintf(' %s%s%s', $option->isValueOptional() ? '[' : '', \strtoupper($option->getName()), $option->isValueOptional() ? ']' : '');
                }
                $shortcut = $option->getShortcut() ? \sprintf('-%s|', $option->getShortcut()) : '';
                $negation = $option->isNegatable() ? \sprintf('|--no-%s', $option->getName()) : '';
                $elements[] = \sprintf('[%s--%s%s%s]', $shortcut, $option->getName(), $value, $negation);
            }
        }
        if (\count($elements) && $this->getArguments()) {
            $elements[] = '[--]';
        }
        $tail = '';
        foreach ($this->getArguments() as $argument) {
            $element = '<' . $argument->getName() . '>';
            if ($argument->isArray()) {
                $element .= '...';
            }
            if (!$argument->isRequired()) {
                $element = '[' . $element;
                $tail .= ']';
            }
            $elements[] = $element;
        }
        return \implode(' ', $elements) . $tail;
    }
}
