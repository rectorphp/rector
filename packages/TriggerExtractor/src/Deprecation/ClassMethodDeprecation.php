<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\Deprecation;

use Rector\TriggerExtractor\Contract\Deprecation\DeprecationInterface;

final class ClassMethodDeprecation implements DeprecationInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $oldMethod;

    /**
     * @var string
     */
    private $newMethod;

    /**
     * @var mixed[]
     */
    private $newArguments = [];

    public function __construct(string $class, string $oldMethod, string $newMethod, array $newArguments = [])
    {
        $this->class = $class;
        $this->oldMethod = $oldMethod;
        $this->newMethod = $newMethod;
        $this->newArguments = $newArguments;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getOldMethod(): string
    {
        return $this->oldMethod;
    }

    public function getNewMethod(): string
    {
        return $this->newMethod;
    }

    /**
     * @return mixed[]
     */
    public function getNewArguments(): array
    {
        return $this->newArguments;
    }
}
