<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

final class ArgumentRemoverRecipeFactory extends AbstractArgumentRecipeFactory
{
    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): ArgumentRemoverRecipe
    {
        $this->validateArrayData($data);

        return new ArgumentRemoverRecipe($data['class'], $data['method'], $data['position']);
    }
}
