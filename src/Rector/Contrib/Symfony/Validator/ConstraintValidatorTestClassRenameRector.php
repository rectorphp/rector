<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Validator;

use Rector\Rector\AbstractChangeParentClassRector;

/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#validator
 * Symfony\Component\Validator\Test\ConstraintValidatorTestCase
 */
final class ConstraintValidatorTestClassRenameRector extends AbstractChangeParentClassRector
{
    protected function getOldClassName(): string
    {
        return 'Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest';
    }

    protected function getNewClassName(): string
    {
        return 'Symfony\Component\Validator\Test\ConstraintValidatorTestCase';
    }
}
