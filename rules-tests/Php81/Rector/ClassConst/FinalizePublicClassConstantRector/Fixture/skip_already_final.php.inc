<?php

namespace Rector\Tests\Php81\Rector\ClassConst\FinalizePublicClassConstantRector\Fixture;

final class SkipAlreadyFinal
{
    final public const NAME = 'value';
}
