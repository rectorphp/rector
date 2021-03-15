<?php

namespace Rector\Tests\Symfony4\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

class SkipAnonymousClass
{
    public function buildForm($builder): void
    {
        $builder->add(
            new class() {
            }
        );
    }
}
