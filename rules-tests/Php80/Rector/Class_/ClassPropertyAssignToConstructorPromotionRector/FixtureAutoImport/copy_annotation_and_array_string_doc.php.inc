<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\FixtureAutoImport;

use Symfony\Component\Serializer\Annotation\SerializedName;

class CopyAnnotationAndArrayStringDoc
{
    /**
     * @var string[]
     * @SerializedName("owsy/derived-facts")
     */
    private array $derivedFacts;

    public function __construct(
        array $derivedFacts = [],
    ) {
        $this->derivedFacts = $derivedFacts;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\FixtureAutoImport;

use Symfony\Component\Serializer\Annotation\SerializedName;

class CopyAnnotationAndArrayStringDoc
{
    /**
     * @param string[] $derivedFacts
     */
    public function __construct(
        /**
         * @SerializedName("owsy/derived-facts")
         */
        private array $derivedFacts = []
    )
    {
    }
}

?>
