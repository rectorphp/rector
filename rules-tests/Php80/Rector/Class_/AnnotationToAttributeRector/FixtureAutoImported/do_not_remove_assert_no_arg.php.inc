<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixtureAutoImported;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

class DoNotREmoveAssertNoArg
{
    /**
     * @ORM\Column(type="string", length=27)
     * @Assert\NotBlank()
     * @Assert\Length(max=27)
     */
    private string $email;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixtureAutoImported;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

class DoNotREmoveAssertNoArg
{
    #[ORM\Column(type: 'string', length: 27)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 27)]
    private string $email;
}

?>
