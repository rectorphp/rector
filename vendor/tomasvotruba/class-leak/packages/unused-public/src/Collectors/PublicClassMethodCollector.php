<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\UnusedPublic\Collectors;

use RectorPrefix202608\Doctrine\Common\EventSubscriber;
use RectorPrefix202608\Illuminate\Console\Command;
use RectorPrefix202608\JMS\Serializer\Handler\SubscribingHandlerInterface;
use RectorPrefix202608\Livewire\Component;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ClassReflection;
use RectorPrefix202608\Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RectorPrefix202608\TomasVotruba\UnusedPublic\ApiDocStmtAnalyzer;
use RectorPrefix202608\TomasVotruba\UnusedPublic\Configuration;
use RectorPrefix202608\TomasVotruba\UnusedPublic\MethodTypeDetector;
use RectorPrefix202608\TomasVotruba\UnusedPublic\PublicClassMethodMatcher;
use RectorPrefix202608\Twig\Extension\ExtensionInterface;
/**
 * @implements Collector<ClassMethod, array{class-string, string, int}>
 */
final class PublicClassMethodCollector implements Collector
{
    /**
     * @readonly
     */
    private ApiDocStmtAnalyzer $apiDocStmtAnalyzer;
    /**
     * @readonly
     */
    private PublicClassMethodMatcher $publicClassMethodMatcher;
    /**
     * @readonly
     */
    private MethodTypeDetector $methodTypeDetector;
    /**
     * @readonly
     */
    private Configuration $configuration;
    /**
     * @var string[]
     */
    private const SKIPPED_TYPES = [
        // symfony
        'RectorPrefix202608\Symfony\Component\EventDispatcher\EventSubscriberInterface',
        // doctrine
        EventSubscriber::class,
        SubscribingHandlerInterface::class,
        ExtensionInterface::class,
        Controller::class,
        // laravel
        Command::class,
        Component::class,
        'RectorPrefix202608\Illuminate\Http\Request',
        'RectorPrefix202608\Illuminate\Contracts\Mail\Mailable',
        'RectorPrefix202608\Illuminate\Contracts\Queue\ShouldQueue',
        'RectorPrefix202608\Illuminate\Support\ServiceProvider',
    ];
    public function __construct(ApiDocStmtAnalyzer $apiDocStmtAnalyzer, PublicClassMethodMatcher $publicClassMethodMatcher, MethodTypeDetector $methodTypeDetector, Configuration $configuration)
    {
        $this->apiDocStmtAnalyzer = $apiDocStmtAnalyzer;
        $this->publicClassMethodMatcher = $publicClassMethodMatcher;
        $this->methodTypeDetector = $methodTypeDetector;
        $this->configuration = $configuration;
    }
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }
    /**
     * @param ClassMethod $node
     * @return array{class-string, string, int}|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (!$this->configuration->shouldCollectMethods()) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if ($this->shouldSkip($classReflection, $node, $scope)) {
            return null;
        }
        if ($this->publicClassMethodMatcher->shouldSkipClassMethod($node)) {
            return null;
        }
        if ($this->apiDocStmtAnalyzer->isApiDoc($node, $classReflection)) {
            return null;
        }
        if ($this->isSkippedType($classReflection)) {
            return null;
        }
        if ($this->publicClassMethodMatcher->shouldSkipClassReflection($classReflection)) {
            return null;
        }
        $methodName = $node->name->toString();
        // is this method required by parent contract? skip it
        if ($this->publicClassMethodMatcher->isUsedByParentClassOrInterface($classReflection, $methodName)) {
            return null;
        }
        return [$classReflection->getName(), $methodName, $node->getLine()];
    }
    private function shouldSkip(ClassReflection $classReflection, ClassMethod $classMethod, Scope $scope): bool
    {
        // skip acceptance tests, codeception
        if (substr_compare($classReflection->getName(), 'Cest', -strlen('Cest')) === 0) {
            return \true;
        }
        if ($this->methodTypeDetector->isTestMethod($classMethod, $scope)) {
            return \true;
        }
        return $this->methodTypeDetector->isTraitMethod($classMethod, $scope);
    }
    private function isSkippedType(ClassReflection $classReflection): bool
    {
        $found = \false;
        foreach (self::SKIPPED_TYPES as $skippedType) {
            if ($classReflection->isSubclassOf($skippedType)) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
}
