<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

final class ArgumentDefaultValueReplacerRecipe extends AbstractArgumentRecipe
{
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
    public function __construct(string $class, string $method, int $position, $before, $after)
    {
        parent::__construct($class, $method, $position);
        $this->before = $before;
        $this->after = $after;
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
