<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

use Rector\Configuration\Rector\Guard\RecipeGuard;
use Rector\Contract\Configuration\Rector\ArgumentRecipeInterface;
use Rector\Rector\Argument\ArgumentRemoverRector;

final class ArgumentRemoverRecipe implements ArgumentRecipeInterface
{
    /**
     * @var null|string
     */
    private $value;

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

    private function __construct(string $class, string $method, int $position, ?string $value)
    {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->value = $value;
    }

    /**
     * @param mixed[] $array
     */
    public static function createFromArray(array $array): self
    {
        RecipeGuard::ensureHasKeys($array, ['class', 'method', 'position'], ArgumentRemoverRector::class);

        return new self($array['class'], $array['method'], $array['position'], $array['value'] ?? null);
    }

    public function getValue(): ?string
    {
        return $this->value;
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
}
