<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Answer
{
    /**
     * @ORM\ManyToMany(targetEntity="RemoveInversedBy", inversedBy="answers")
     */
    private $voters;

    public function getVoters()
    {
        return $this->voters;
    }
}

$answer = new Answer();
$answer->getVoters();

/**
 * @ORM\Entity
 */
class RemoveInversedBy
{
    /**
     * @ORM\ManyToMany(targetEntity="Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Fixture\Answer", mappedBy="voters")
     */
    private $answers;

    public function __construct()
    {
        $this->answers = new ArrayCollection;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Answer
{
    /**
     * @ORM\ManyToMany(targetEntity="RemoveInversedBy")
     */
    private $voters;

    public function getVoters()
    {
        return $this->voters;
    }
}

$answer = new Answer();
$answer->getVoters();

/**
 * @ORM\Entity
 */
class RemoveInversedBy
{
    public function __construct()
    {
    }
}

?>
