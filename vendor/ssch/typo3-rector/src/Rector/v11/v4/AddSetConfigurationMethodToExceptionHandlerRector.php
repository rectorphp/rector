<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.4/Deprecation-95009-PassingTypoScriptConfigurationAsConstructorArgumentToExceptionHandler.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v4\AddSetConfigurationMethodToExceptionHandlerRector\AddSetConfigurationMethodToExceptionHandlerRectorTest
 */
final class AddSetConfigurationMethodToExceptionHandlerRector extends AbstractRector
{
    /**
     * @var string
     */
    private const SET_CONFIGURATION = 'setConfiguration';
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
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
        if ($this->shouldSkip($node)) {
            return null;
        }
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        $configurationMethod = $this->createSetConfigurationMethod();
        $node->stmts[] = new Nop();
        $node->stmts[] = $configurationMethod;
        if (!$constructClassMethod instanceof ClassMethod) {
            return $node;
        }
        $firstParameterName = (string) $this->getName($constructClassMethod->params[0]);
        // Add all statements from constructor to new configuration method
        $configurationMethod->stmts = (array) $constructClassMethod->stmts;
        $this->renameFirstConstructorParameterVariableName($constructClassMethod, $firstParameterName);
        $constructClassMethod->stmts = [];
        if ('' === $firstParameterName) {
            return $node;
        }
        // Remove the old configuration parameter
        unset($constructClassMethod->params[0]);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add method setConfiguration to class which implements ExceptionHandlerInterface', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Frontend\ContentObject\Exception\ExceptionHandlerInterface;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;

class CustomExceptionHandler implements ExceptionHandlerInterface
{
    private array $configuration;

    public function __construct(array $configuration) {
        $this->configuration = $configuration;
    }

    public function handle(\Exception $exception, AbstractContentObject $contentObject = null, $contentObjectConfiguration = [])
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Frontend\ContentObject\Exception\ExceptionHandlerInterface;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;

class CustomExceptionHandler implements ExceptionHandlerInterface
{
    private array $configuration;

    public function handle(\Exception $exception, AbstractContentObject $contentObject = null, $contentObjectConfiguration = [])
    {
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }
}
CODE_SAMPLE
)]);
    }
    private function shouldSkip(Class_ $class) : bool
    {
        if (!$this->nodeTypeResolver->isObjectType($class, new ObjectType('TYPO3\\CMS\\Frontend\\ContentObject\\Exception\\ExceptionHandlerInterface'))) {
            return \true;
        }
        $className = $this->getName($class);
        if (null === $className) {
            return \true;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return \true;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if ($classReflection->hasMethod(self::SET_CONFIGURATION)) {
            return \true;
        }
        return null !== $class->getMethod(self::SET_CONFIGURATION);
    }
    private function createSetConfigurationMethod() : ClassMethod
    {
        $configurationMethod = $this->nodeFactory->createPublicMethod(self::SET_CONFIGURATION);
        $configurationVariable = new Variable('configuration');
        $configurationParam = new Param($configurationVariable);
        $configurationParam->type = new Identifier('array');
        $configurationMethod->params[] = $configurationParam;
        $configurationMethod->returnType = new Identifier('void');
        return $configurationMethod;
    }
    private function renameFirstConstructorParameterVariableName(ClassMethod $constructClassMethod, string $firstParameterName) : void
    {
        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->find((array) $constructClassMethod->stmts, function (Node $node) use($firstParameterName) {
            if (!$node instanceof Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $firstParameterName);
        });
        if ([] === $variables) {
            return;
        }
        foreach ($variables as $variable) {
            $variable->name = 'configuration';
        }
    }
}
