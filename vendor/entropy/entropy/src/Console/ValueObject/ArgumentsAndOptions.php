<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Console\ValueObject;

use RectorPrefix202609\Webmozart\Assert\Assert;
final class ArgumentsAndOptions
{
    /**
     * @var Argument[]
     * @readonly
     */
    private array $arguments;
    /**
     * @var Option[]
     * @readonly
     */
    private array $options;
    /**
     * @param Argument[] $arguments
     * @param Option[] $options
     */
    public function __construct(array $arguments, array $options)
    {
        $this->arguments = $arguments;
        $this->options = $options;
        Assert::allIsInstanceOf($arguments, Argument::class);
        Assert::allIsInstanceOf($options, Option::class);
    }
    /**
     * @return Argument[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
