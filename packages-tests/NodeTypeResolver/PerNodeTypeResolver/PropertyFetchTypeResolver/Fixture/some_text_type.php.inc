<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\DocBlockProperties;

final class SomeTextType
{
    public function run(DocBlockProperties $docBlockProperties): void
    {
        $docBlockProperties->text->xxx();
    }
}

?>
-----
<?php

use PHPStan\Type\StringType;

return new StringType();
