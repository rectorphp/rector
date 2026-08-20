<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony34\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Symfony\DataProvider\ServiceMapProvider;
use Rector\Symfony\NodeAnalyzer\ServiceTypeMethodCallResolver;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony34\Rector\Closure\ContainerGetNameToTypeInTestsRector\ContainerGetNameToTypeInTestsRectorTest
 */
final class ContainerGetNameToTypeInTestsRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private ServiceTypeMethodCallResolver $serviceTypeMethodCallResolver;
    /**
     * @readonly
     */
    private ServiceMapProvider $serviceMapProvider;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ServiceTypeMethodCallResolver $serviceTypeMethodCallResolver, ServiceMapProvider $serviceMapProvider)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->serviceTypeMethodCallResolver = $serviceTypeMethodCallResolver;
        $this->serviceMapProvider = $serviceMapProvider;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/dependency-injection', '>=3.4');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change $container->get("some_name") in tests to bare type, useful since Symfony 3.4', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function run()
    {
        $container = $this->getContainer();
        $someClass = $container->get('some_name');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function run()
    {
        $container = $this->getContainer();
        $someClass = $container->get(SomeType::class);
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->name, 'get')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('Symfony\Component\DependencyInjection\ContainerInterface'))) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $args = $node->getArgs();
        $firstArg = $args[0];
        if (!$firstArg->value instanceof String_) {
            return null;
        }
        $serviceType = $this->serviceTypeMethodCallResolver->resolve($node);
        if (!$serviceType instanceof ObjectType) {
            return null;
        }
        // only replace when the resolved class is itself a registered service id (real service or alias),
        // otherwise the named service maps to a class that has no matching FQCN service and the call breaks
        $className = $serviceType->getClassName();
        if (!$this->serviceMapProvider->provide()->hasService($className)) {
            return null;
        }
        $classConstFetch = new ClassConstFetch(new FullyQualified($className), 'class');
        $node->args[0] = new Arg($classConstFetch);
        return $node;
    }
}
