<?php

namespace Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

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
