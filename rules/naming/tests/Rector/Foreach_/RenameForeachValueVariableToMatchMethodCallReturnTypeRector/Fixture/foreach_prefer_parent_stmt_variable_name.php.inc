<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Trait_;

class ForeachPreferParentStmtVariableName
{
    public function run()
    {
        $searchInNodes = [];
        foreach ($this->getUsedTraits() as $trait) {
            $trait = $this->findTrait($trait);
            if ($trait === null) {
                continue;
            }
            $searchInNodes[] = $trait;
        }
    }

    /**
     * @return Name[]
     */
    public function getUsedTraits(): array
    {
    }

    public function findTrait(string $name): Trait_
    {
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Trait_;

class ForeachPreferParentStmtVariableName
{
    public function run()
    {
        $searchInNodes = [];
        foreach ($this->getUsedTraits() as $name) {
            $name = $this->findTrait($name);
            if ($name === null) {
                continue;
            }
            $searchInNodes[] = $name;
        }
    }

    /**
     * @return Name[]
     */
    public function getUsedTraits(): array
    {
    }

    public function findTrait(string $name): Trait_
    {
    }
}

?>
