<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoInstanceOfStaticReflectionRule\Fixture;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;

final class SkipArrayClassString
{
    /**
     * @var array<class-string<Stmt>>
     */
    private const BEFORE_TRAIT_TYPES = [TraitUse::class, Property::class, ClassMethod::class];

    public function find($node)
    {
        foreach (self::BEFORE_TRAIT_TYPES as $beforeTraitType) {
            if (is_a($node, $beforeTraitType, true)) {
                return true;
            }
        }

        return false;
    }
}
