<?php

namespace Rector\Symfony4\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

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
