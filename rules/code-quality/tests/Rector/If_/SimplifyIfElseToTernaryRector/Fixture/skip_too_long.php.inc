<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfElseToTernaryRector\Fixture;

class SkipTooLong
{
    public function run()
    {
        if ($this->nodeTypeResolver->isStringyType($staticCall->args[1]->value)) {
            $name = $this->nameResolver->isName(
                $staticCall,
                'contains'
            ) ? 'assertStringContainsString' : 'assertStringNotContainsString';
        } else {
            $name = $this->nameResolver->isName($staticCall, 'contains') ? 'assertContains' : 'assertNotContains';
        }
    }
}
