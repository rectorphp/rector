<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\RenameParamDefaultInit\Fixture;

use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;

class DoNotAddDefaultValueInitRenamedVariableFromParam
{
    public function hasTruePseudoType(BracketsAwareUnionTypeNode $type): bool
    {
        $unionTypes = $type->types;

        foreach ($unionTypes as $unionType) {}
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\RenameParamDefaultInit\Fixture;

use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;

class DoNotAddDefaultValueInitRenamedVariableFromParam
{
    public function hasTruePseudoType(BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode): bool
    {
        $unionTypes = $bracketsAwareUnionTypeNode->types;

        foreach ($unionTypes as $unionType) {}
    }
}

?>
