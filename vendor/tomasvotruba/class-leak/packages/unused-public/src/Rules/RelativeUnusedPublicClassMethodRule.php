<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\UnusedPublic\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Collectors\PublicClassMethodCollector;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Configuration;
use RectorPrefix202609\TomasVotruba\UnusedPublic\NodeCollectorExtractor;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Templates\TemplateMethodCallsProvider;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Templates\UsedMethodAnalyzer;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Utils\Strings;
/**
 * @see \TomasVotruba\UnusedPublic\Tests\Rules\RelativeUnusedPublicClassMethodRule\RelativeUnusedPublicClassMethodRuleTest
 */
final class RelativeUnusedPublicClassMethodRule implements Rule
{
    /**
     * @readonly
     */
    private Configuration $configuration;
    /**
     * @readonly
     */
    private UsedMethodAnalyzer $usedMethodAnalyzer;
    /**
     * @readonly
     */
    private TemplateMethodCallsProvider $templateMethodCallsProvider;
    /**
     * @readonly
     */
    private NodeCollectorExtractor $nodeCollectorExtractor;
    /**
     * @api
     * @var string
     */
    public const ERROR_MESSAGE = 'Found %.1f %% of public methods as unused. Reduce it under %.1f %%';
    public function __construct(Configuration $configuration, UsedMethodAnalyzer $usedMethodAnalyzer, TemplateMethodCallsProvider $templateMethodCallsProvider, NodeCollectorExtractor $nodeCollectorExtractor)
    {
        $this->configuration = $configuration;
        $this->usedMethodAnalyzer = $usedMethodAnalyzer;
        $this->templateMethodCallsProvider = $templateMethodCallsProvider;
        $this->nodeCollectorExtractor = $nodeCollectorExtractor;
    }
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }
    /**
     * @param CollectedDataNode $node
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->configuration->isUnusedRelativeMethodEnabled()) {
            return [];
        }
        $twigMethodNames = $this->templateMethodCallsProvider->provideTwigMethodCalls();
        $bladeMethodNames = $this->templateMethodCallsProvider->provideBladeMethodCalls();
        $completeMethodCallReferences = $this->nodeCollectorExtractor->extractMethodCallReferences($node);
        // php method calls are case-insensitive
        $lowerCompleteMethodCallReferences = Strings::lowercase($completeMethodCallReferences);
        $publicMethodCount = 0;
        $unusedPublicMethodCount = 0;
        $filePathToLines = [];
        $publicClassMethodCollector = $node->get(PublicClassMethodCollector::class);
        foreach ($publicClassMethodCollector as $filePath => $declarations) {
            foreach ($declarations as [$className, $methodName, $line]) {
                ++$publicMethodCount;
                if ($this->isUsedClassMethod($className, $methodName, $lowerCompleteMethodCallReferences, $twigMethodNames, $bladeMethodNames)) {
                    continue;
                }
                $filePathToLines[$filePath][] = $line;
                ++$unusedPublicMethodCount;
            }
        }
        // unable to divide by 0
        if ($publicMethodCount === 0) {
            return [];
        }
        $relativeUnusedPublicMethods = $unusedPublicMethodCount / $publicMethodCount * 100;
        $maximumRelativeAllowed = $this->configuration->getMaximumRelativeUnusedPublicMethod();
        // is withing limit?
        if ($relativeUnusedPublicMethods < $maximumRelativeAllowed) {
            return [];
        }
        return $this->createRuleErrors($filePathToLines, $relativeUnusedPublicMethods, $maximumRelativeAllowed);
    }
    /**
     * @param string[] $lowerCompleteMethodCallReferences
     * @param string[] $twigMethodNames
     * @param string[] $bladeMethodNames
     */
    private function isUsedClassMethod(string $className, string $methodName, array $lowerCompleteMethodCallReferences, array $twigMethodNames, array $bladeMethodNames): bool
    {
        if ($this->usedMethodAnalyzer->isUsedInTwig($methodName, $twigMethodNames)) {
            return \true;
        }
        if (in_array($methodName, $bladeMethodNames, \true)) {
            return \true;
        }
        $methodReference = $className . '::' . $methodName;
        return in_array(strtolower($methodReference), $lowerCompleteMethodCallReferences, \true);
    }
    /**
     * @param array<string, int[]> $filePathToLines
     * @return RuleError[]
     * @param int|float $maximumLimit
     */
    private function createRuleErrors(array $filePathToLines, float $relative, $maximumLimit): array
    {
        $ruleErrors = [];
        foreach ($filePathToLines as $filePath => $lines) {
            foreach ($lines as $line) {
                $errorMessage = sprintf(self::ERROR_MESSAGE, $relative, $maximumLimit);
                $ruleErrors[] = RuleErrorBuilder::message($errorMessage)->file($filePath)->line($line)->identifier('public.method.unused')->build();
            }
        }
        return $ruleErrors;
    }
}
