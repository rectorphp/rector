<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

use Rector\Core\Exception\ShouldNotHappenException;

final class KeyDefinedAsString
{
    /**
     * @param array<string, string> $data
     */
    public function getKey(array $data, string $value)
    {
        foreach ($data as $key => $d) {
            if ($d !== $value) {
                continue;
            }

            return $key;
        }

        throw new ShouldNotHappenException();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

use Rector\Core\Exception\ShouldNotHappenException;

final class KeyDefinedAsString
{
    /**
     * @param array<string, string> $data
     */
    public function getKey(array $data, string $value): string
    {
        foreach ($data as $key => $d) {
            if ($d !== $value) {
                continue;
            }

            return $key;
        }

        throw new ShouldNotHappenException();
    }
}

?>
