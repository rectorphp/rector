<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SkipAnonymousFunction
{
    /**
     * @var ContentFinder
     */
    private $contentFinder;

    public function __construct(ContentFinder $contentFinder)
    {
        $this->contentFinder = $contentFinder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $content = $this->contentFinder->someMethod((int) ($event->getData()['content']));
            $this->somePrivateMethod();
            // ...
        });
    }

    private function somePrivateMethod(): void {
    }
}
