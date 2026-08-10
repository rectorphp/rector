<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\Scalar;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\Transform\ValueObject\ScalarValueToConstFetch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as the scalar value target is too wide. The very same value can be used in an unrelated context, e.g. an array key, a version number or a message, and get replaced by a constant that does not belong there. Use a custom rule scoped to the exact context instead.
 */
final class ScalarValueToConstFetchRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces Scalar values with a ConstFetch or ClassConstFetch', [new ConfiguredCodeSample(<<<'SAMPLE'
$var = 10;
SAMPLE
, <<<'SAMPLE'
$var = \SomeClass::FOOBAR_INT;
SAMPLE
, [new ScalarValueToConstFetch(new Int_(10), new ClassConstFetch(new FullyQualified('SomeClass'), new Identifier('FOOBAR_INT')))])]);
    }
    public function getNodeTypes(): array
    {
        return [String_::class, Float_::class, Int_::class];
    }
    /**
     * @param String_|Float_|Int_ $node
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\ClassConstFetch|null
     */
    public function refactor(Node $node)
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as the scalar value target is too wide and replaces values in unrelated contexts; use a custom rule scoped to the exact context instead', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
    }
}
