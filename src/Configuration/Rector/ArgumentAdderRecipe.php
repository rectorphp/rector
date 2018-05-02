<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

final class ArgumentAdderRecipe extends AbstractArgumentReplacerRecipe
{
    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @param mixed $defaultValue
     */
    public function __construct(string $class, string $method, int $position, $defaultValue = null)
    {
        parent::__construct($class, $method, $position);
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
