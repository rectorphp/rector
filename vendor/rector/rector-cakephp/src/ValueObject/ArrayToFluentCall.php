<?php

declare (strict_types=1);
namespace Rector\CakePHP\ValueObject;

final class ArrayToFluentCall
{
    /**
     * @readonly
     * @var string
     */
    private $class;
    /**
     * @var array<string, string>
     * @readonly
     */
    private $arrayKeysToFluentCalls;
    /**
     * @param array<string, string> $arrayKeysToFluentCalls
     */
    public function __construct(string $class, array $arrayKeysToFluentCalls)
    {
        $this->class = $class;
        $this->arrayKeysToFluentCalls = $arrayKeysToFluentCalls;
    }
    public function getClass() : string
    {
        return $this->class;
    }
    /**
     * @return array<string, string>
     */
    public function getArrayKeysToFluentCalls() : array
    {
        return $this->arrayKeysToFluentCalls;
    }
}
