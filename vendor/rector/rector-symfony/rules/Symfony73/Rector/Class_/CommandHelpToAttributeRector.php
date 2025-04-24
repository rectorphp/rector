<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitor;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\CommandMethodName;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/doc/current/console.html#help-message
 *
 * @see \Rector\Symfony\Tests\Symfony73\Rector\Class_\CommandHelpToAttributeRector\CommandHelpToAttributeRectorTest
 */
final class CommandHelpToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Moves $this->setHelp() to the "help" named argument of #[AsCommand]', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command;

final class SomeCommand extends Command
{
    protected function configure(): void
    {
        $this->setHelp('Some help text');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'app:some', help: <<<'TXT'
Some help text
TXT)]
final class SomeCommand extends Command
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->isAbstract()) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass(SymfonyAttribute::AS_COMMAND)) {
            return null;
        }
        if (!$this->isObjectType($node, new ObjectType(SymfonyClass::COMMAND))) {
            return null;
        }
        $asCommandAttribute = $this->getAsCommandAttribute($node);
        if ($asCommandAttribute === null) {
            return null;
        }
        foreach ($asCommandAttribute->args as $arg) {
            if ((($nullsafeVariable1 = $arg->name) ? $nullsafeVariable1->toString() : null) === 'help') {
                return null;
            }
        }
        $configureClassMethod = $node->getMethod(CommandMethodName::CONFIGURE);
        if (!$configureClassMethod instanceof ClassMethod) {
            return null;
        }
        $helpExpr = $this->findAndRemoveSetHelpExpr($configureClassMethod);
        if (!$helpExpr instanceof String_) {
            return null;
        }
        $wrappedHelp = new String_($helpExpr->value, [Attributekey::KIND => String_::KIND_NOWDOC, AttributeKey::DOC_LABEL => 'TXT']);
        $asCommandAttribute->args[] = new Arg($wrappedHelp, \false, \false, [], new Identifier('help'));
        if ($configureClassMethod->stmts === []) {
            unset($configureClassMethod);
        }
        return $node;
    }
    private function getAsCommandAttribute(Class_ $class) : ?Attribute
    {
        foreach ($class->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if ($this->nodeNameResolver->isName($attribute->name, SymfonyAttribute::AS_COMMAND)) {
                    return $attribute;
                }
            }
        }
        return null;
    }
    /**
     * Returns the argument passed to setHelp() and removes the MethodCall node.
     */
    private function findAndRemoveSetHelpExpr(ClassMethod $configureMethod) : ?String_
    {
        $helpString = null;
        $this->traverseNodesWithCallable((array) $configureMethod->stmts, function (Node $node) use(&$helpString) {
            if ($node instanceof Class_ || $node instanceof Function_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'setHelp')) {
                return null;
            }
            if ($node->isFirstClassCallable() || !isset($node->getArgs()[0])) {
                return null;
            }
            $argExpr = $node->getArgs()[0]->value;
            if ($argExpr instanceof String_) {
                $helpString = $argExpr;
            }
            $parent = $node->getAttribute('parent');
            if ($parent instanceof Expression) {
                unset($parent);
            }
            return $node->var;
        });
        foreach ((array) $configureMethod->stmts as $key => $stmt) {
            if ($this->isExpressionVariableThis($stmt)) {
                unset($configureMethod->stmts[$key]);
            }
        }
        return $helpString;
    }
    private function isExpressionVariableThis(Stmt $stmt) : bool
    {
        if (!$stmt instanceof Expression) {
            return \false;
        }
        if (!$stmt->expr instanceof Variable) {
            return \false;
        }
        return $this->isName($stmt->expr, 'this');
    }
}
