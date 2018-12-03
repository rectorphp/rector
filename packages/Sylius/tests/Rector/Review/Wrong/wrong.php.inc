<?php

namespace Rector\Sylius\Tests\Rector\Review\Wrong;

class SomeClass
{
    /**
     * @var \Sylius\Component\Review\Factory\ReviewFactoryInterface
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

namespace Rector\Sylius\Tests\Rector\Review\Wrong;

class SomeClass
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
