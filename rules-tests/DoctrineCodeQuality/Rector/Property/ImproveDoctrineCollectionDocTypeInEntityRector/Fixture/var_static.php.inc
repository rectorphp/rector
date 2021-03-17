<?php

namespace Rector\Tests\DoctrineCodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Fixture;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rector\Tests\DoctrineCodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\Training;

/**
 * @ORM\Entity
 */
class VarStatic
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
     * @var Collection|Training[]
     */
    private $trainings = [];

    /**
     * @var string
     */
    private static $value = '';

    public static function getValue()
    {
        return self::$value;
    }

    public static function setValue(string $value)
    {
        self::$value = $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\DoctrineCodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Fixture;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rector\Tests\DoctrineCodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\Training;

/**
 * @ORM\Entity
 */
class VarStatic
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
     * @var \Rector\Tests\DoctrineCodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\Training[]|\Doctrine\Common\Collections\Collection<int, \Rector\Tests\DoctrineCodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\Training>
     */
    private $trainings = [];

    /**
     * @var string
     */
    private static $value = '';

    public static function getValue()
    {
        return self::$value;
    }

    public static function setValue(string $value)
    {
        self::$value = $value;
    }
}

?>
