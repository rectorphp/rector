<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony;

use Rector\Rector\AbstractChangeParentClassRector;
use Rector\Rector\Set\SetNames;

/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#validator
 *
 * Converts all:
 * Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest
 *
 * into:
 * Symfony\Component\Validator\Test\ConstraintValidatorTestCase
 */
final class ConstraintValidatorTestClassRenameRector extends AbstractChangeParentClassRector
{
    public function getSetName(): string
    {
        return SetNames::SYMFONY;
    }

    public function sinceVersion(): float
    {
        return 4.0;
    }

    protected function getOldClassName(): string
    {
        return 'Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest';
    }

    protected function getNewClassName(): string
    {
        return 'Symfony\Component\Validator\Test\ConstraintValidatorTestCase';
    }
}
