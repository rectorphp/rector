<?php

declare (strict_types=1);
namespace Rector\Symfony\DowngradeSymfony70\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ClassReflection;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\Visibility;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\DowngradeSymfony70\Rector\Class_\DowngradeSymfonyCommandAttributeRector\DowngradeSymfonyCommandAttributeRectorTest
 */
final class DowngradeSymfonyCommandAttributeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade Symfony Command Attribute', [new CodeSample(<<<'CODE_SAMPLE'
#[AsCommand(name: 'app:create-user', description: 'some description')]
class CreateUserCommand extends Command
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[AsCommand(name: 'app:create-user', description: 'some description')]
class CreateUserCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:create-user');
        $this->setDescription('some description');
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if (!$classReflection->isSubClassOf('Symfony\\Component\\Console\\Command\\Command')) {
            return null;
        }
        $resolveNameAndDescription = $this->resolveNameAndDescription($node);
        $name = $resolveNameAndDescription['name'];
        $description = $resolveNameAndDescription['description'];
        if ($name === null && $description === null) {
            return null;
        }
        $configureClassMethod = $node->getMethod('configure');
        $stmts = [];
        if ($name !== null) {
            $stmts[] = new Expression(new MethodCall(new Variable('this'), 'setName', [new Arg($name)]));
        }
        if ($description !== null) {
            $stmts[] = new Expression(new MethodCall(new Variable('this'), 'setDescription', [new Arg($description)]));
        }
        if ($configureClassMethod instanceof ClassMethod) {
            $configureClassMethod->stmts = \array_merge((array) $configureClassMethod->stmts, $stmts);
        } else {
            $classMethod = new ClassMethod('configure');
            $classMethod->flags = Visibility::PROTECTED;
            $classMethod->stmts = $stmts;
            $node->stmts[] = $classMethod;
        }
        foreach ($node->attrGroups as $keyAttribute => $attrGroup) {
            foreach ($attrGroup->attrs as $key => $attr) {
                if ($attr->name->toString() === 'Symfony\\Component\\Console\\Attribute\\AsCommand') {
                    unset($attrGroup->attrs[$key]);
                }
            }
            if ($attrGroup->attrs === []) {
                unset($node->attrGroups[$keyAttribute]);
            }
        }
        return $node;
    }
    /**
     * @return array{name: ?Expr, description: ?Expr}
     */
    private function resolveNameAndDescription(Class_ $class) : array
    {
        $name = null;
        $description = null;
        foreach ($class->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() !== 'Symfony\\Component\\Console\\Attribute\\AsCommand') {
                    continue;
                }
                foreach ($attr->args as $arg) {
                    if (!$arg->name instanceof Identifier) {
                        continue;
                    }
                    if ($arg->name->toString() === 'name') {
                        $name = $arg->value;
                    }
                    if ($arg->name->toString() === 'description') {
                        $description = $arg->value;
                    }
                }
            }
        }
        return ['name' => $name, 'description' => $description];
    }
}
