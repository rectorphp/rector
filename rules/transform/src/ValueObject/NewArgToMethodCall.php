<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class NewArgToMethodCall
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $methodCall;

    /**
     * @param mixed $value
     */
    public function __construct(string $type, $value, string $methodCall)
    {
        $this->type = $type;
        $this->value = $value;
        $this->methodCall = $methodCall;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getMethodCall(): string
    {
        return $this->methodCall;
    }
}
