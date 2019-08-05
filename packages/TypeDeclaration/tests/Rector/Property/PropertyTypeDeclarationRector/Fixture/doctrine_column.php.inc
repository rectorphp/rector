<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineColumn
{
    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $surname;

    /**
     * @ORM\Column(name="date", type="decimal", scale=2, nullable=true)
     */
    private $rate;
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineColumn
{
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $surname;

    /**
     * @ORM\Column(name="date", type="decimal", scale=2, nullable=true)
     * @var float|null
     */
    private $rate;
}

?>
