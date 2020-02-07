<?php

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\SluggableBehaviorRector\Fixture;

use Gedmo\Mapping\Annotation as Gedmo;

class SomeClass
{
    /**
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
}

?>
-----
<?php

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\SluggableBehaviorRector\Fixture;

use Gedmo\Mapping\Annotation as Gedmo;

class SomeClass implements \Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface
{
    use \Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
    /**
     * @return string[]
     */
    public function getSluggableFields(): array
    {
        return ['name'];
    }
}

?>
