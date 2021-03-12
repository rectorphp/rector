<?php

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\BlameableBehaviorRector\Fixture;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @Gedmo\Blameable(on="create")
     */
    private $createdBy;

    /**
     * @Gedmo\Blameable(on="update")
     */
    private $updatedBy;

    /**
     * @Gedmo\Blameable(on="change", field={"title", "body"})
     */
    private $contentChangedBy;

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    public function getContentChangedBy()
    {
        return $this->contentChangedBy;
    }
}

?>
-----
<?php

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\BlameableBehaviorRector\Fixture;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass implements \Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface
{
    use \Knp\DoctrineBehaviors\Model\Blameable\BlameableTrait;
}

?>
