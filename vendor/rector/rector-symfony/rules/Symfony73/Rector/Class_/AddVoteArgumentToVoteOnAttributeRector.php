<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony73\Rector\Class_\AddVoteArgumentToVoteOnAttributeRector\AddVoteArgumentToVoteOnAttributeRectorTest
 */
final class AddVoteArgumentToVoteOnAttributeRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Adds a new `$voter` argument in protected function `voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool`', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class MyVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        return true;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class MyVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?\Symfony\Component\Security\Core\Authorization\Voter\Vote $vote = null): bool
    {
        return true;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $classMethod = null;
        if ($node->extends !== null && $this->isName($node->extends, SymfonyClass::VOTER_CLASS)) {
            $classMethod = $node->getMethod('voteOnAttribute');
        }
        if ($classMethod === null) {
            foreach ($node->implements as $implement) {
                if ($this->isName($implement, SymfonyClass::VOTER_INTERFACE)) {
                    $classMethod = $node->getMethod('vote');
                    break;
                }
            }
        }
        if ($classMethod === null) {
            return null;
        }
        if (count($classMethod->params) !== 3) {
            return null;
        }
        $classMethod->params[] = new Param(new Variable('vote'), new ConstFetch(new Name('null')), new NullableType(new FullyQualified(SymfonyClass::VOTE_CLASS)));
        return $node;
    }
}
