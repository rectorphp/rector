<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Source\AppleJuice;

final class ReturnSpecificType
{
    public function run()
    {
        $values = [];
        $values[] = new AppleJuice();

        return $values;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Source\AppleJuice;

final class ReturnSpecificType
{
    /**
     * @return \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Source\AppleJuice[]
     */
    public function run(): array
    {
        $values = [];
        $values[] = new AppleJuice();

        return $values;
    }
}

?>
