<?php declare (strict_types=1);

class SomeClass
{
    /**
     * @var Sylius\Component\Review\Factory\ReviewFactoryInterface
     */
    private $reviewFactory;

    public function createReview()
    {
        $this->reviewFactory->createForSubject($subject);
    }
}
