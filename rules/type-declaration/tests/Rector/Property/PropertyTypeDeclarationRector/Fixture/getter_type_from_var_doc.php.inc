<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

final class GetterTypeFromVarDoc
{
    private $surname;

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

final class GetterTypeFromVarDoc
{
    /**
     * @var string
     */
    private $surname;

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }
}

?>
