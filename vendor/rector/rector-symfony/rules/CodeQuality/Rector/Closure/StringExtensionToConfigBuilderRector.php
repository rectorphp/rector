<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/blog/new-in-symfony-5-3-config-builder-classes
 *
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\Closure\StringExtensionToConfigBuilderRector\StringExtensionToConfigBuilderRectorTest
 */
final class StringExtensionToConfigBuilderRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector
     */
    private $symfonyPhpClosureDetector;
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add config builder classes', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('security', [
        'providers' => [
            'webservice' => [
                'id' => LoginServiceUserProvider::class,
            ],
        ],
        'firewalls' => [
            'dev' => [
                'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                'security' => false,
            ],
        ],
    ]);
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $securityConfig): void {
    $securityConfig->provider('webservice', [
        'id' => LoginServiceUserProvider::class,
    ]);

    $securityConfig->firewall('dev', [
        'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
        'security' => false,
    ]);
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->symfonyPhpClosureDetector->detect($node)) {
            return null;
        }
        // must be exactly one
        if (\count($node->stmts) !== 1) {
            return null;
        }
        $onlyStmt = $node->stmts[0];
        if (!$onlyStmt instanceof Expression) {
            return null;
        }
        if (!$onlyStmt->expr instanceof MethodCall) {
            return null;
        }
        $methodCall = $onlyStmt->expr;
        if (!$this->isName($methodCall->name, 'extension')) {
            return null;
        }
        $args = $methodCall->getArgs();
        $firstArg = $args[0];
        $extensionKey = $this->valueResolver->getValue($firstArg->value);
        if ($extensionKey !== 'security') {
            return null;
        }
        $fullyQualified = new FullyQualified('Symfony\\Config\\SecurityConfig');
        $node->params[0] = new Param(new Variable('securityConfig'), null, $fullyQualified);
        // we have a match
        $secondArg = $args[1];
        $configuration = $secondArg->value;
        if (!$configuration instanceof Array_) {
            return null;
        }
        $securityConfigVariable = new Variable('securityConfig');
        $fluentMethodCall = null;
        $configurationValues = $this->valueResolver->getValue($configuration);
        foreach ($configurationValues as $key => $value) {
            if ($key === 'providers') {
                $methodCallName = 'provider';
            } elseif ($key === 'firewalls') {
                $methodCallName = 'firewall';
            } else {
                throw new NotImplementedYetException($key);
            }
            foreach ($value as $itemName => $itemConfiguration) {
                $args = $this->nodeFactory->createArgs([$itemName, $itemConfiguration]);
                if (!$fluentMethodCall instanceof MethodCall) {
                    $fluentMethodCall = new MethodCall($securityConfigVariable, $methodCallName, $args);
                } else {
                    $fluentMethodCall = new MethodCall($fluentMethodCall, $methodCallName, $args);
                }
            }
        }
        if (!$fluentMethodCall instanceof MethodCall) {
            return null;
        }
        $node->stmts = [new Expression($fluentMethodCall)];
        return $node;
    }
}
