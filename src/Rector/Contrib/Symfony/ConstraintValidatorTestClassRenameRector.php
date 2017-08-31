<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractRector;

/**
 * @todo decouple abstract class AbstractReplaceParentClass
 *
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#validator
 *
 * Converts all:
 * Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest
 *
 * into:
 * Symfony\Component\Validator\Test\ConstraintValidatorTestCase
 */
final class ConstraintValidatorTestClassRenameRector extends AbstractRector
{
    public function getSetName(): string
    {
        return SetNames::SYMFONY;
    }

    public function sinceVersion(): float
    {
        return 4.0;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        if (! $node->extends) {
            return false;
        }

        /** @var Node\Name $parentClassName */
        $parentClassNameNode = $node->extends;

        /** @var Node\Name\FullyQualified $fsqName */
        $fsqName = $parentClassNameNode->getAttribute('resolvedName');

        return $fsqName->toString() === $this->getOldClassName();
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->extends = new Node\Name('\\' . $this->getNewClassName());
        return $node;
    }

    private function getOldClassName(): string
    {
        return 'Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest';
    }

    private function getNewClassName(): string
    {
        return 'Symfony\Component\Validator\Test\ConstraintValidatorTestCase';
    }
}
