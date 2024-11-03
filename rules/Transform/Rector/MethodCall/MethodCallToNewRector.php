<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Transform\ValueObject\MethodCallToNew;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\MethodCall\MethodCallToNewRector\MethodCallToNewRectorTest
 */
class MethodCallToNewRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var MethodCallToNew[]
     */
    private $methodCallToNew;
    /**
     * @param MethodCallToNew[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, MethodCallToNew::class);
        $this->methodCallToNew = $configuration;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change method call to new class', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$object->createResponse(['a' => 1]);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
new Response(['a' => 1]);
CODE_SAMPLE
, [new MethodCallToNew(new ObjectType('ResponseFactory'), 'createResponse', 'Response')])]);
    }
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?New_
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        foreach ($this->methodCallToNew as $methodCallToNew) {
            if (!$this->isName($node->name, $methodCallToNew->getMethodName())) {
                continue;
            }
            if (!$this->isObjectType($node->var, $methodCallToNew->getObject())) {
                continue;
            }
            return new New_(new FullyQualified($methodCallToNew->getNewClassString()), $node->args);
        }
        return null;
    }
}
