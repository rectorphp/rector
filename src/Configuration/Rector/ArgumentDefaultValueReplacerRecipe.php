<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

final class ArgumentDefaultValueReplacerRecipe extends AbstractArgumentReplacerRecipe
{
    /**
     * @var mixed[]
     */
    private $replacement = [];

    /**
     * @param mixed $replacement
     */
    public function __construct(string $class, string $method, int $position, $replacement = [])
    {
        parent::__construct($class, $method, $position);
        $this->replacement = $replacement;
    }

    /**
     * @return mixed[]
     */
    public function getReplacement(): array
    {
        return $this->replacement;
    }
}
