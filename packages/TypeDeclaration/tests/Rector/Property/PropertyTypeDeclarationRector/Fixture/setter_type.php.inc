<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

final class SetterType
{
    private $email;
    private $name;

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

final class SetterType
{
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $name;

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}

?>
