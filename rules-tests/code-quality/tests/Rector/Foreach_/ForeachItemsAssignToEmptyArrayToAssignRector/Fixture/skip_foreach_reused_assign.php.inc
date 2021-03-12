<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

class SkipForeachReusedAssign
{
    private $includes = ['a' => 1];

    public function run()
    {
        $currentAlphabet = [];

        // Includes
        foreach (\array_keys($this->includes) as $alphabet) {
            foreach ($this->resolveAlphabetCharacters($alphabet) as $char) {
                $currentAlphabet[] = $char;
            }
        }
    }

    private function resolveAlphabetCharacters($alphabet): array
    {
        return ['a', 'b', 'c'];
    }
}
