<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

final class ArgumentAdderRecipeFactory extends AbstractArgumentReplacerRecipeFactory
{
    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): ArgumentAdderRecipe
    {
        $this->validateArrayData($data);

        return new ArgumentAdderRecipe(
            $data['class'],
            $data['method'],
            $data['position'],
            $data['replacement'] ?? null
        );
    }
}
