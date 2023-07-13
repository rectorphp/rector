<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector\FixtureAutoImport;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Rector\Doctrine\Tests\CodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector\FixtureAutoImport\Category;

/**
 * @ORM\Table(name="articles")
 * @ORM\Entity(repositoryClass="Domain\Repository\ArticleRepository")
 */
class DoNotrepetitiveAssign
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected ?\DateTimeInterface $createdAt;

    /**
     * @var Collection|Category[]
     *
     * @ORM\ManyToMany(targetEntity="Category", cascade={"persist", "merge"})
     * @ORM\JoinTable(name="articles_categories")
     */
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector\FixtureAutoImport;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="articles")
 * @ORM\Entity(repositoryClass="Domain\Repository\ArticleRepository")
 */
class DoNotrepetitiveAssign
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected ?DateTimeInterface $createdAt;

    /**
     * @var Collection|Category[]
     *
     * @ORM\ManyToMany(targetEntity="Category", cascade={"persist", "merge"})
     * @ORM\JoinTable(name="articles_categories")
     */
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

}

?>
