<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

use Rector\Configuration\Rector\Guard\RecipeGuard;
use Rector\Contract\Configuration\Rector\ArgumentRecipeInterface;
use Rector\Rector\Dynamic\ArgumentAdderRector;

final class ArgumentAdderRecipe implements ArgumentRecipeInterface
{
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
     * @var mixed
     */
    private $defaultValue;

    /**
     * @param mixed $defaultValue
     */
    private function __construct(string $class, string $method, int $position, $defaultValue = null)
    {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param mixed[] $array
     */
    public static function createFromArray(array $array): self
    {
        RecipeGuard::ensureHasKeys($array, ['class', 'method', 'position'], ArgumentAdderRector::class);

        return new self($array['class'], $array['method'], $array['position'], $array['default_value'] ?? null);
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

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
