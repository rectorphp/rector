<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

class SymfonyConstraints
{
    /**
     * @Assert\Type("string")
     * @Assert\Length(max="180")
     * @Assert\Email(groups={"registration"})
     * @Assert\NotNull(groups={"registration"})
     */
    private $email;
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

class SymfonyConstraints
{
    #[Assert\Type('string')]
    #[Assert\Length(max: '180')]
    #[Assert\Email(groups: ['registration'])]
    #[Assert\NotNull(groups: ['registration'])]
    private $email;
}

?>
