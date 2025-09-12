<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://3v4l.org/NS419
 * @see https://3v4l.org/nMOpl
 * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_the_sleep_and_wakeup_magic_methods
 * @see \Rector\Tests\Php85\Rector\Class_\WakeupToUnserializeRector\WakeupToUnserializeRectorTest
 */
final class WakeupToUnserializeRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATED_METHOD_WAKEUP;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change __wakeup() to __unserialize()', [new CodeSample(<<<'CODE_SAMPLE'
class User {
    public function __wakeup() {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class User {
    public function __unserialize(array $data): void{
        foreach ($data as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
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
        if ($node->getMethod('__unserialize') instanceof ClassMethod) {
            return null;
        }
        $classMethod = $node->getMethod('__wakeup');
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        $classMethod->name = new Identifier('__unserialize');
        $classMethod->returnType = new Identifier('void');
        $param = new Param(new Variable('data'), null, new Identifier('array'));
        $classMethod->params[] = $param;
        $classMethod->stmts = [$this->assignProperties()];
        return $node;
    }
    private function assignProperties(): Foreach_
    {
        $assign = new Assign(new PropertyFetch(new Variable('this'), new Variable('property')), new Variable('value'));
        $if = new If_(new FuncCall(new Name('property_exists'), [new Arg(new Variable('this')), new Arg(new Variable('property'))]), ['stmts' => [new Expression($assign)]]);
        return new Foreach_(new Variable('data'), new Variable('value'), ['keyVar' => new Variable('property'), 'stmts' => [$if]]);
    }
}
