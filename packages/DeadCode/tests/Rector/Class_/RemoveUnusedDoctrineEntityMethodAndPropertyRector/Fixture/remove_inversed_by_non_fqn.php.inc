<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Question
{
    /**
     * @ORM\ManyToMany(targetEntity="RemoveInversedByNonFqn", inversedBy="answers")
     */
    private $voters;

    public function getVoters()
    {
        return $this->voters;
    }
}

$answer = new Question();
$answer->getVoters();

/**
 * @ORM\Entity
 */
class RemoveInversedByNonFqn
{
    /**
     * @ORM\ManyToMany(targetEntity="Question", mappedBy="voters")
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
class Question
{
    /**
     * @ORM\ManyToMany(targetEntity="RemoveInversedByNonFqn")
     */
    private $voters;

    public function getVoters()
    {
        return $this->voters;
    }
}

$answer = new Question();
$answer->getVoters();

/**
 * @ORM\Entity
 */
class RemoveInversedByNonFqn
{
    public function __construct()
    {
    }
}

?>
