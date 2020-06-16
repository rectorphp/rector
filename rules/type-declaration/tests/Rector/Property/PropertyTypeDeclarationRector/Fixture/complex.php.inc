<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use App\Api\Poll\Entity\Poll;
use App\Api\User\Entity\User;
use App\Api\User\Entity\UserList;
use App\Entity\LocalizedString;
use App\Entity\LocalizedStringList;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="answer")
 * @ORM\Entity
 */
class Complex
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LocalizedString", mappedBy="answerText", cascade={"all"})
     * @ORM\JoinColumn(nullable=false)
     * @ORM\OrderBy({"version" = "DESC"})
     */
    private $answer;

    /**
     * @ORM\ManyToMany(targetEntity="App\Api\User\Entity\User", inversedBy="answers", cascade={"persist", "merge"})
     */
    private $voters;

    public function getAnswer(): LocalizedStringList
    {
        if ($this->answer instanceof Collection) {
            return new LocalizedStringList($this->answer->getValues());
        }

        if ($this->answer instanceof LocalizedStringList) {
            return $this->answer;
        }

        return new LocalizedStringList($this->answer);
    }

    /**
     * @param LocalizedStringList|LocalizedString[]|ArrayCollection $answer
     */
    public function setAnswer($answer): void
    {
        if ($answer instanceof LocalizedStringList) {
            $this->answer = $answer->getLocalizedStrings();

            return;
        }

        if ($answer instanceof ArrayCollection) {
            $this->answer = $answer->getValues();

            return;
        }

        $this->answer = $answer;
    }

    public function getVoters(): UserList
    {
        if ($this->voters instanceof Collection) {
            return new UserList($this->voters->getValues());
        }

        if ($this->voters instanceof UserList) {
            return $this->voters;
        }

        return new UserList($this->voters);
    }

    /**
     * @param UserList|User[]|ArrayCollection $voters
     */
    public function setVoters($voters): void
    {
        if ($voters instanceof UserList) {
            $this->voters = $voters->getUsers();

            return;
        }

        if ($voters instanceof ArrayCollection) {
            $this->voters = $voters->getValues();

            return;
        }

        $this->voters = $voters;
    }

    public function addVoter(User $user): void
    {
        $this->voters[] = $user;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use App\Api\Poll\Entity\Poll;
use App\Api\User\Entity\User;
use App\Api\User\Entity\UserList;
use App\Entity\LocalizedString;
use App\Entity\LocalizedStringList;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="answer")
 * @ORM\Entity
 */
class Complex
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LocalizedString", mappedBy="answerText", cascade={"all"})
     * @ORM\JoinColumn(nullable=false)
     * @ORM\OrderBy({"version" = "DESC"})
     * @var \App\Entity\LocalizedString[]|\Doctrine\Common\Collections\Collection
     */
    private $answer;

    /**
     * @ORM\ManyToMany(targetEntity="App\Api\User\Entity\User", inversedBy="answers", cascade={"persist", "merge"})
     * @var \App\Api\User\Entity\User[]|\Doctrine\Common\Collections\Collection
     */
    private $voters;

    public function getAnswer(): LocalizedStringList
    {
        if ($this->answer instanceof Collection) {
            return new LocalizedStringList($this->answer->getValues());
        }

        if ($this->answer instanceof LocalizedStringList) {
            return $this->answer;
        }

        return new LocalizedStringList($this->answer);
    }

    /**
     * @param LocalizedStringList|LocalizedString[]|ArrayCollection $answer
     */
    public function setAnswer($answer): void
    {
        if ($answer instanceof LocalizedStringList) {
            $this->answer = $answer->getLocalizedStrings();

            return;
        }

        if ($answer instanceof ArrayCollection) {
            $this->answer = $answer->getValues();

            return;
        }

        $this->answer = $answer;
    }

    public function getVoters(): UserList
    {
        if ($this->voters instanceof Collection) {
            return new UserList($this->voters->getValues());
        }

        if ($this->voters instanceof UserList) {
            return $this->voters;
        }

        return new UserList($this->voters);
    }

    /**
     * @param UserList|User[]|ArrayCollection $voters
     */
    public function setVoters($voters): void
    {
        if ($voters instanceof UserList) {
            $this->voters = $voters->getUsers();

            return;
        }

        if ($voters instanceof ArrayCollection) {
            $this->voters = $voters->getValues();

            return;
        }

        $this->voters = $voters;
    }

    public function addVoter(User $user): void
    {
        $this->voters[] = $user;
    }
}

?>
