<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\Symfony\Enum\SymfonyClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.3.md#console
 *
 * @see \Rector\Symfony\Tests\Symfony73\Rector\Class_\CommandDefaultNameAndDescriptionToAsCommandAttributeRector\CommandDefaultNameAndDescriptionToAsCommandAttributeRectorTest
 */
final class CommandDefaultNameAndDescriptionToAsCommandAttributeRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace getDefaultName() and getDefaultDescription() by #[AsCommand] attribute', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command;

final class SomeCommand extends Command
{
    public static function getDefaultName(): string
    {
        return 'app:some-command';
    }

    public static function getDefaultDescription(): string
    {
        return 'This is some command description';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(
    name: 'app:some-command',
    description: 'This is some command description'
)]
final class SomeCommand extends Command
{
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
    public function refactor(Node $node): ?Class_
    {
        if (!$this->isObjectType($node, new ObjectType(SymfonyClass::COMMAND))) {
            return null;
        }
        $defaultName = null;
        $defaultDescription = null;
        foreach ($node->stmts as $key => $classStmt) {
            if (!$classStmt instanceof ClassMethod) {
                continue;
            }
            if ($classStmt->stmts === null) {
                continue;
            }
            if ($this->isName($classStmt, 'getDefaultName')) {
                $soleStmt = $classStmt->stmts[0];
                if ($soleStmt instanceof Return_) {
                    $defaultName = $soleStmt->expr;
                    unset($node->stmts[$key]);
                }
            }
            if ($this->isName($classStmt, 'getDefaultDescription')) {
                $soleStmt = $classStmt->stmts[0];
                if ($soleStmt instanceof Return_) {
                    $defaultDescription = $soleStmt->expr;
                    unset($node->stmts[$key]);
                }
            }
        }
        if (!$defaultName instanceof Expr && !$defaultDescription instanceof Expr) {
            return null;
        }
        $args = [];
        if ($defaultName instanceof Expr) {
            $args[] = new Arg($defaultName, \false, \false, [], new Identifier('name'));
        }
        if ($defaultDescription instanceof Expr) {
            $args[] = new Arg($defaultDescription, \false, \false, [], new Identifier('description'));
        }
        $node->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(SymfonyAttribute::AS_COMMAND), $args)]);
        return $node;
    }
}
