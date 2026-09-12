<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\SetUpAssignedPropertyTyper;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\TypedPropertyFromGetRepositorySetUpRector\TypedPropertyFromGetRepositorySetUpRectorTest
 */
final class TypedPropertyFromGetRepositorySetUpRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private SetUpAssignedPropertyTyper $setUpAssignedPropertyTyper;
    public function __construct(SetUpAssignedPropertyTyper $setUpAssignedPropertyTyper)
    {
        $this->setUpAssignedPropertyTyper = $setUpAssignedPropertyTyper;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add strict typed property to a private test case property based on its @var object type, when assigned via entity manager getRepository() fetch in setUp()', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @var SomeEntityRepository
     */
    private $someEntityRepository;

    protected function setUp(): void
    {
        $this->someEntityRepository = $this->em->getRepository(SomeEntity::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private SomeEntityRepository $someEntityRepository;

    protected function setUp(): void
    {
        $this->someEntityRepository = $this->em->getRepository(SomeEntity::class);
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
        return $this->setUpAssignedPropertyTyper->refactorClass($node, fn(Expr $expr): bool => $this->isGetRepositoryCall($expr));
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    private function isGetRepositoryCall(Expr $expr): bool
    {
        if (!$expr instanceof MethodCall) {
            return \false;
        }
        return $this->isName($expr->name, 'getRepository');
    }
}
