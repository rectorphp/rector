<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#httpfoundation
 * @see \Rector\Symfony\Tests\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector\BinaryFileResponseCreateToNewInstanceRectorTest
 */
final class BinaryFileResponseCreateToNewInstanceRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change deprecated BinaryFileResponse::create() to use __construct() instead', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation;

class SomeClass
{
    public function run()
    {
        $binaryFile = BinaryFileResponse::create();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation;

class SomeClass
{
    public function run()
    {
        $binaryFile = new BinaryFileResponse(null);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->class instanceof Name) {
            return null;
        }
        if (!$this->isName($node->class, 'Symfony\\Component\\HttpFoundation\\BinaryFileResponse')) {
            return null;
        }
        if (!$this->isName($node->name, 'create')) {
            return null;
        }
        $args = $node->args;
        if ($args === []) {
            $args[] = $this->nodeFactory->createArg($this->nodeFactory->createNull());
        }
        return new New_($node->class, $args);
    }
}
