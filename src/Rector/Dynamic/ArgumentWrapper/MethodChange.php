<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic\ArgumentWrapper;

use Assert\Assertion;

final class MethodChange
{
    /**
     * @var string
     */
    private const TYPE_ADDED = 'added';

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var int
     */
    private $position;

    /**
     * @var string
     */
    private $type;

    /**
     * @var mixed|null
     */
    private $defaultValue;

    /**
     * @param mixed $defaultValue
     */
    private function __construct(
        string $class,
        string $method,
        int $position,
        string $type = self::TYPE_ADDED,
        $defaultValue = null
    ) {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->type = $type;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param mixed[] $methodChange
     */
    public static function createFromMethodChange(array $methodChange): self
    {
        self::ensureMethodChangeIsValid($methodChange);

        return new self(
            $methodChange['class'],
            $methodChange['method'],
            $methodChange['position'],
            $methodChange['type'],
            $methodChange['default_value'] ?? null
        );
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $methodChange
     */
    private static function ensureMethodChangeIsValid(array $methodChange): void
    {
        Assertion::keyExists($methodChange, 'class');
        Assertion::string($methodChange['class']);

        Assertion::keyExists($methodChange, 'method');
        Assertion::string($methodChange['method']);

        Assertion::keyExists($methodChange, 'position');
        Assertion::integer($methodChange['position']);

        Assertion::keyExists($methodChange, 'type');
        Assertion::string($methodChange['type']);
    }
}
