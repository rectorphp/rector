<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

use Rector\Configuration\Rector\Guard\RecipeGuard;
use Rector\Contract\Configuration\Rector\ArgumentRecipeInterface;
use Rector\Rector\Dynamic\ArgumentDefaultValueReplacerRector;

final class ArgumentDefaultValueReplacerRecipe implements ArgumentRecipeInterface
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
    private $before;

    /**
     * @var mixed
     */
    private $after;

    /**
     * @param mixed $before
     * @param mixed $after
     */
    private function __construct(string $class, string $method, int $position, $before, $after)
    {
        $this->before = $before;
        $this->after = $after;
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
    }

    /**
     * @param mixed[] $array
     */
    public static function createFromArray(array $array): self
    {
        RecipeGuard::ensureHasKeys(
            $array,
            ['class', 'method', 'position', 'before', 'after'],
            ArgumentDefaultValueReplacerRector::class
        );

        return new self($array['class'], $array['method'], $array['position'], $array['before'], $array['after']);
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
     * @return mixed
     */
    public function getBefore()
    {
        return $this->before;
    }

    /**
     * @return mixed
     */
    public function getAfter()
    {
        return $this->after;
    }
}
