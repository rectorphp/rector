<?php

namespace Rector\Core\Tests\Issues\AddConstructMethodNoScope\Fixture;

use App\Entity\Sage\FDocligne;
use App\Repository\Sage\FDocenteteRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table]
#[ORM\Entity(repositoryClass: FDocenteteRepository::class, readOnly: true)]
class FDocentete
{
    #[ORM\OneToMany(mappedBy: 'doPiece', targetEntity: FDocligne::class)]
    #[ORM\JoinColumn(referencedColumnName: 'DO_Piece')]
    private ?Collection $fDoclignes = null;
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\AddConstructMethodNoScope\Fixture;

use App\Entity\Sage\FDocligne;
use App\Repository\Sage\FDocenteteRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table]
#[ORM\Entity(repositoryClass: FDocenteteRepository::class, readOnly: true)]
class FDocentete
{
    #[ORM\OneToMany(mappedBy: 'doPiece', targetEntity: FDocligne::class)]
    #[ORM\JoinColumn(referencedColumnName: 'DO_Piece')]
    private ?Collection $fDoclignes = null;
    public function __construct()
    {
        $this->fDoclignes = new \Doctrine\Common\Collections\ArrayCollection();
    }
}

?>
