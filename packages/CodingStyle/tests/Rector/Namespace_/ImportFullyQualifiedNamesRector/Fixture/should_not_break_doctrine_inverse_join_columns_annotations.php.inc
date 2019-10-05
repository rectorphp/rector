<?php

declare(strict_types=1);

namespace Page;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="page_template")
 * @ORM\Entity()
 */
class Template
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Area[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Area", cascade={"remove", "persist"}, inversedBy="templates")
     * @ORM\JoinTable(name="page_template_area",
     *      joinColumns={@ORM\JoinColumn(name="template_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="area_id", referencedColumnName="id")}
     * )
     */
    private $areas;

    public function __construct()
    {
        $this->areas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function addArea(Area $area): self
    {
        $this->areas[] = $area;

        return $this;
    }

    public function removeArea(Area $area): self
    {
        $this->areas->removeElement($area);

        return $this;
    }

    /**
     * @return Area[]|ArrayCollection
     */
    public function getAreas()
    {
        return $this->areas;
    }
}

/**
 * @ORM\Table(name="Area",indexes={@ORM\Index(name="area_name_idx", columns={"name"})})
 * @ORM\Entity()
 */
class Area
{
    /**
     * @var Template[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Template", mappedBy="areas")
     */
    private $templates;

    public function __construct()
    {
        $this->templates = new ArrayCollection();
    }

    public function addTemplate(Template $template): self
    {
        $this->templates[] = $template;

        return $this;
    }

    public function removeTemplate(Template $template): self
    {
        $this->templates->removeElement($template);

        return $this;
    }

    /**
     * @return Template[]|ArrayCollection
     */
    public function getTemplates()
    {
        return $this->templates;
    }
}
