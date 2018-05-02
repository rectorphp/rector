<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

final class ArgumentDefaultValueReplacerRecipeFactory extends AbstractArgumentReplacerRecipeFactory
{
    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): ArgumentDefaultValueReplacerRecipe
    {
        $this->validateArrayData($data);

        return new ArgumentDefaultValueReplacerRecipe(
            $data['class'],
            $data['method'],
            $data['position'],
            $data['default_value'] ?? []
        );
    }
}
