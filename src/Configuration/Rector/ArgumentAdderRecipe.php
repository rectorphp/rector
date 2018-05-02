<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

final class ArgumentAdderRecipe extends AbstractArgumentReplacerRecipe
{
    /**
     * @var mixed
     */
    private $replacement;

    /**
     * @param mixed $replacement
     */
    public function __construct(string $class, string $method, int $position, $replacement = null)
    {
        parent::__construct($class, $method, $position);
        $this->replacement = $replacement;
    }

    /**
     * @return mixed|null
     */
    public function getReplacement()
    {
        return $this->replacement;
    }
}
