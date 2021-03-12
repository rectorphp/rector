<?php

declare(strict_types=1);

namespace Rector\Tests\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Source;

interface VideoFormFactory
{
    public function create(): VideoForm;
}
