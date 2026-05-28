<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony81\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/blob/8.1/UPGRADE-8.1.md#uid
 *
 * @see \Rector\Symfony\Tests\Symfony81\Rector\StaticCall\AddFormatArgumentToIsValidRector\AddFormatArgumentToIsValidRectorTest
 */
final class AddFormatArgumentToIsValidRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add $format argument to Ulid::isValid()', [new CodeSample(<<<'CODE_SAMPLE'
Symfony\Component\Uid\Ulid;

final class Foo
{
    public function bar(string $id): bool
    {
        return Ulid::isValid($id);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
Symfony\Component\Uid\Ulid;

final class Foo
{
    public function bar(string $id): bool
    {
        return Ulid::isValid($id, Symfony\Component\Uid\Ulid::FORMAT_BASE_32);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->class, 'Symfony\Component\Uid\Ulid')) {
            return null;
        }
        if (!$this->isName($node->name, 'isValid')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (\count($node->args) !== 1) {
            return null;
        }
        $node->args[] = new Arg(new ClassConstFetch(new FullyQualified(SymfonyClass::ULID_CLASS), 'FORMAT_BASE_32'));
        return $node;
    }
}
