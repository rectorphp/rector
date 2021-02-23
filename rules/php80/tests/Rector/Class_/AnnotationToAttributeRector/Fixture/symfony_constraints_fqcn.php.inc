<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

class SymfonyConstraints
{
    /**
     * @\Symfony\Component\Validator\Constraints\Type("string")
     * @\Symfony\Component\Validator\Constraints\Length(max="180")
     * @\Symfony\Component\Validator\Constraints\Email(groups={"registration"})
     * @\Symfony\Component\Validator\Constraints\NotNull(groups={"registration"})
     */
    private $email;
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

class SymfonyConstraints
{
    #[\Symfony\Component\Validator\Constraints\Type('string')]
    #[\Symfony\Component\Validator\Constraints\Length(max: '180')]
    #[\Symfony\Component\Validator\Constraints\Email(groups: ['registration'])]
    #[\Symfony\Component\Validator\Constraints\NotNull(groups: ['registration'])]
    private $email;
}

?>
