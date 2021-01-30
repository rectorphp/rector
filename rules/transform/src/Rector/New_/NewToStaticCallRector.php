<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\NewToStaticCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Transform\Tests\Rector\New_\NewToStaticCallRector\NewToStaticCallRectorTest
 */
final class NewToStaticCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TYPE_TO_STATIC_CALLS = 'type_to_static_calls';

    /**
     * @var NewToStaticCall[]
     */
    private $typeToStaticCalls = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change new Object to static call', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        new Cookie($name);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        Cookie::create($name);
    }
}
CODE_SAMPLE
                ,
                [
                    self::TYPE_TO_STATIC_CALLS => [new NewToStaticCall('Cookie', 'Cookie', 'create')],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->typeToStaticCalls as $typeToStaticCall) {
            if (! $this->isObjectType($node->class, $typeToStaticCall->getType())) {
                continue;
            }

            return $this->nodeFactory->createStaticCall(
                $typeToStaticCall->getStaticCallClass(),
                $typeToStaticCall->getStaticCallMethod(),
                $node->args
            );
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $typeToStaticCalls = $configuration[self::TYPE_TO_STATIC_CALLS] ?? [];
        Assert::allIsInstanceOf($typeToStaticCalls, NewToStaticCall::class);
        $this->typeToStaticCalls = $typeToStaticCalls;
    }
}
