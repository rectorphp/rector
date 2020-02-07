<?php

namespace Rector\Sylius\Tests\Rector\Review\Fixture;

use Sylius\Component\Review\Factory\ReviewFactoryInterface;

class SomeClass
{
    /**
     * @var ReviewFactoryInterface
     */
    private $reviewFactory;

    public function createReview()
    {
        $this->reviewFactory->createForSubjectWithReviewer($subject);
    }
}

?>
-----
<?php

namespace Rector\Sylius\Tests\Rector\Review\Fixture;

use Sylius\Component\Review\Factory\ReviewFactoryInterface;

class SomeClass
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
