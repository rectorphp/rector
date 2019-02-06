<?php

namespace Rector\Sylius\Tests\Rector\Review\Fixture;

use Rector\Sylius\Tests\Rector\Review\Source\ReviewFactoryInterface;

class SomeClass2
{
    /**
     * @var ReviewFactoryInterface
     */
    private $reviewFactory;

    public function createReview()
    {
        $this->reviewFactory->createForSubjectWithReviewer($subject, NULL);
    }
}

?>
-----
<?php

namespace Rector\Sylius\Tests\Rector\Review\Fixture;

use Rector\Sylius\Tests\Rector\Review\Source\ReviewFactoryInterface;

class SomeClass2
{
    /**
     * @var ReviewFactoryInterface
     */
    private $reviewFactory;

    public function createReview()
    {
        $this->reviewFactory->createForSubject($subject);
    }
}

?>
