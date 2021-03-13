<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\DocBlockProperties;

final class ExplicitMixed
{
    public function run(DocBlockProperties $docBlockProperties): void
    {
        $docBlockProperties->explicitMixed->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\MixedType;

return new MixedType();
