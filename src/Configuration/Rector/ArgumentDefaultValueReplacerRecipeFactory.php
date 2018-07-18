<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

final class ArgumentDefaultValueReplacerRecipeFactory extends AbstractArgumentRecipeFactory
{
    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): ArgumentDefaultValueReplacerRecipe
    {
        $this->validateArrayData($data);

        $this->ensureHasKey($data, 'before');
        $this->ensureHasKey($data, 'after');

        return new ArgumentDefaultValueReplacerRecipe(
            $data['class'],
            $data['method'],
            $data['position'],
            $data['before'],
            $data['after']
        );
    }
}
