<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Fixture;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\DocBlockProperties;

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
