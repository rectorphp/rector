<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\VarDumperTestTraitMethodArgsRector\VarDumperTestTraitMethodArgsRectorTest
 */
final class VarDumperTestTraitMethodArgsRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Adds a new `$filter` argument in `VarDumperTestTrait->assertDumpEquals()` and `VarDumperTestTrait->assertDumpMatchesFormat()` in Validator in Symfony.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$varDumperTestTrait->assertDumpEquals($dump, $data, $message = "");', '$varDumperTestTrait->assertDumpEquals($dump, $data, $filter = 0, $message = "");'), new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $message = "");', '$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $filter = 0, $message = "");')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Symfony\\Component\\VarDumper\\Test\\VarDumperTestTrait'))) {
            return null;
        }
        if (!$this->isNames($node->name, ['assertDumpEquals', 'assertDumpMatchesFormat'])) {
            return null;
        }
        if (\count($node->args) <= 2) {
            return null;
        }
        $secondArg = $node->args[2];
        if (!$secondArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        if ($secondArg->value instanceof \PhpParser\Node\Expr\ConstFetch) {
            return null;
        }
        if ($secondArg->value instanceof \PhpParser\Node\Scalar\String_) {
            $node->args[3] = $node->args[2];
            $node->args[2] = $this->nodeFactory->createArg(0);
            return $node;
        }
        return null;
    }
}
