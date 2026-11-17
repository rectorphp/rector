<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony51\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
/**
 * @see https://github.com/symfony/symfony/pull/35858
 * @see \Rector\Symfony\Tests\Symfony51\Rector\String_\PreviousAdminRoleToIsImpersonatorRector\PreviousAdminRoleToIsImpersonatorRectorTest
 */
final class PreviousAdminRoleToIsImpersonatorRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/security-core', '>=5.1');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change deprecated "ROLE_PREVIOUS_ADMIN" role to "IS_IMPERSONATOR" attribute', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SomeController extends AbstractController
{
    public function run()
    {
        return $this->isGranted('ROLE_PREVIOUS_ADMIN');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SomeController extends AbstractController
{
    public function run()
    {
        return $this->isGranted('IS_IMPERSONATOR');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [String_::class];
    }
    /**
     * @param String_ $node
     */
    public function refactor(Node $node): ?String_
    {
        if ($node->value !== 'ROLE_PREVIOUS_ADMIN') {
            return null;
        }
        return new String_('IS_IMPERSONATOR');
    }
}
