<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Source\ExternalConstant;

final class IncludeExternalConstants
{
    /**
     * @return array<string, string|int>
     */
    public function run(int $line, string $file): array
    {
        return [
            ExternalConstant::LINE => $line,
            ExternalConstant::FILE => $file,
        ];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Source\ExternalConstant;

final class IncludeExternalConstants
{
    /**
     * @return array{line: int, file: string}
     */
    public function run(int $line, string $file): array
    {
        return [
            ExternalConstant::LINE => $line,
            ExternalConstant::FILE => $file,
        ];
    }
}

?>
