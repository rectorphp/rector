<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PHPStan\Type\ObjectType;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\Transform\ValueObject\MethodCallToNew;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as not used, based on assumptions of factory method body and requires manual work.
 */
final class MethodCallToNewRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    /**
     * @param MethodCallToNew[] $configuration
     */
    public function configure(array $configuration): void
    {
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change method call to new class', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$object->createResponse(['a' => 1]);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
new Response(['a' => 1]);
CODE_SAMPLE
, [new MethodCallToNew(new ObjectType('ResponseFactory'), 'createResponse', 'Response')])]);
    }
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?New_
    {
        throw new ShouldNotHappenException(sprintf('%s as not used, based on assumptions of factory method body and requires manual work.', self::class));
    }
}
