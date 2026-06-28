<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Enum_;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\Enum_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as risky change that does not handle enum usage across the whole context. Handle it via IDE with full context, manually, or a custom rule instead.
 */
final class EnumCaseToPascalCaseRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert enum cases to PascalCase and update their usages', [new CodeSample(<<<'CODE_SAMPLE'
enum Status
{
    case PENDING;
    case published;
    case IN_REVIEW;
    case waiting_for_approval;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
enum Status
{
    case Pending;
    case Published;
    case InReview;
    case WaitingForApproval;
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Enum_::class, ClassConstFetch::class];
    }
    /**
     * @param Enum_|ClassConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated as risky change that does not handle enum usage across the whole context. Handle it via IDE with full context, manually, or a custom rule instead', self::class));
    }
}
