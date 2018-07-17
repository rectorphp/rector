<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

final class ArgumentRemoverRecipe extends AbstractArgumentRecipe
{
    /**
     * @var null|string
     */
    private $value;

    public function __construct(string $class, string $method, int $position, ?string $value)
    {
        parent::__construct($class, $method, $position);
        $this->value = $value;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
