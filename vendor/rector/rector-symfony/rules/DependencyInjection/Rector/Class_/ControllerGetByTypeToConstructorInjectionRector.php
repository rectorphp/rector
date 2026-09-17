<?php

declare (strict_types=1);
namespace Rector\Symfony\DependencyInjection\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\Symfony\DependencyInjection\ContainerGetToConstructorInjectionReplacer;
use Rector\Symfony\Enum\SymfonyClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\DependencyInjection\Rector\Class_\ControllerGetByTypeToConstructorInjectionRector\ControllerGetByTypeToConstructorInjectionRectorTest
 */
final class ControllerGetByTypeToConstructorInjectionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ContainerGetToConstructorInjectionReplacer $containerGetToConstructorInjectionReplacer;
    public function __construct(ContainerGetToConstructorInjectionReplacer $containerGetToConstructorInjectionReplacer)
    {
        $this->containerGetToConstructorInjectionReplacer = $containerGetToConstructorInjectionReplacer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('From `$container->get(SomeType::class)` in controllers to constructor injection (step 1/x)', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeCommand extends Controller
{
    public function someMethod()
    {
        $someType = $this->get(SomeType::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeCommand extends Controller
{
    public function __construct(private SomeType $someType)
    {
    }

    public function someMethod()
    {
        $someType = $this->someType;
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
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        if (!$this->containerGetToConstructorInjectionReplacer->replace($node)) {
            return null;
        }
        return $node;
    }
    private function shouldSkipClass(Class_ $class): bool
    {
        // keep it safe
        if (!$class->isFinal()) {
            return \true;
        }
        $scope = ScopeFetcher::fetch($class);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        return !$classReflection->is(SymfonyClass::CONTROLLER);
    }
}
