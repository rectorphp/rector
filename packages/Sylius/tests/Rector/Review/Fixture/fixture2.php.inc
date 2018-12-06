<?php

namespace Rector\Sylius\Tests\Rector\Review\Wrong;

class SomeClass2
{
    /**
     * @var \Sylius\Component\Review\Factory\ReviewFactoryInterface
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

namespace Rector\Sylius\Tests\Rector\Review\Wrong;

class SomeClass2
{
    /**
     * @var \Sylius\Component\Review\Factory\ReviewFactoryInterface
     */
    private $reviewFactory;

    public function createReview()
    {
        $this->reviewFactory->createForSubject($subject);
    }
}

?>
