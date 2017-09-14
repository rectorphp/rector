<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Deprecation;

use Nette\Utils\Strings;
use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;

final class ClassMethodDeprecation implements DeprecationInterface
{
    /**
     * @var string
     */
    private $oldMethod;

    /**
     * @var string
     */
    private $newMethod;

    /**
     * @var string
     */
    private $oldClass;

    /**
     * @var string
     */
    private $newClass;

    /**
     * @var mixed[]
     */
    private $oldArguments = [];

    /**
     * @var mixed[]
     */
    private $newArguments = [];

    /**
     * @param mixed[] $oldArguments
     * @param mixed[] $newArguments
     */
    public function __construct(
        string $oldMethod,
        string $newMethod,
        array $oldArguments = [],
        array $newArguments = []
    ) {
        if (Strings::contains($oldMethod, '::')) {
            [$this->oldClass, $this->oldMethod] = explode('::', $oldMethod);
        } else {
            $this->oldMethod = $oldMethod;
        }

        $this->oldMethod = rtrim($this->oldMethod, '()');

        if (Strings::contains($newMethod, '::')) {
            [$this->newClass, $this->newMethod] = explode('::', $newMethod);
        } else {
            $this->newMethod = $newMethod;
        }

        $this->newMethod = rtrim($this->newMethod, '()');

        $this->oldArguments = $oldArguments;
        $this->newArguments = $newArguments;
    }

    public function getOldClass(): string
    {
        return $this->oldClass;
    }

    public function getNewClass(): string
    {
        return $this->newClass;
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
    public function getOldArguments(): array
    {
        return $this->oldArguments;
    }

    /**
     * @return mixed[]
     */
    public function getNewArguments(): array
    {
        return $this->newArguments;
    }
}
