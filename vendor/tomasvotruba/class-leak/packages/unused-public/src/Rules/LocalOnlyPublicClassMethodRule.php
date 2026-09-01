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
use RectorPrefix202609\TomasVotruba\UnusedPublic\Enum\RuleTips;
use RectorPrefix202609\TomasVotruba\UnusedPublic\NodeCollectorExtractor;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Templates\TemplateMethodCallsProvider;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Templates\UsedMethodAnalyzer;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Utils\Strings;
/**
 * @see \TomasVotruba\UnusedPublic\Tests\Rules\LocalOnlyPublicClassMethodRule\LocalOnlyPublicClassMethodRuleTest
 */
final class LocalOnlyPublicClassMethodRule implements Rule
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
    public const ERROR_MESSAGE = 'Public method "%s::%s()" is used only locally and should be turned protected/private';
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
        if (!$this->configuration->isLocalMethodEnabled()) {
            return [];
        }
        $twigMethodNames = $this->templateMethodCallsProvider->provideTwigMethodCalls();
        $localAndExternalMethodCallReferences = $this->nodeCollectorExtractor->extractLocalAndExternalMethodCallReferences($node);
        // php method calls are case-insensitive
        $lowerExternalRefs = Strings::lowercase($localAndExternalMethodCallReferences->getExternalMethodCallReferences());
        $lowerLocalRefs = Strings::lowercase($localAndExternalMethodCallReferences->getLocalMethodCallReferences());
        $ruleErrors = [];
        $publicClassMethodCollector = $node->get(PublicClassMethodCollector::class);
        foreach ($publicClassMethodCollector as $filePath => $declarations) {
            foreach ($declarations as [$className, $methodName, $line]) {
                if (!$this->isUsedOnlyLocally($className, $methodName, $lowerExternalRefs, $lowerLocalRefs, $twigMethodNames)) {
                    continue;
                }
                /** @var string $methodName */
                $errorMessage = sprintf(self::ERROR_MESSAGE, $className, $methodName);
                $ruleErrors[] = RuleErrorBuilder::message($errorMessage)->file($filePath)->line($line)->tip(RuleTips::NARROW_SCOPE)->identifier('public.method.unused')->build();
            }
        }
        return $ruleErrors;
    }
    /**
     * @param string[] $lowerExternalRefs
     * @param string[] $lowerLocalRefs
     * @param string[] $twigMethodNames
     */
    private function isUsedOnlyLocally(string $className, string $methodName, array $lowerExternalRefs, array $lowerLocalRefs, array $twigMethodNames): bool
    {
        if ($this->usedMethodAnalyzer->isUsedInTwig($methodName, $twigMethodNames)) {
            return \true;
        }
        $publicMethodReference = strtolower($className . '::' . $methodName);
        if (in_array($publicMethodReference, $lowerExternalRefs, \true)) {
            return \false;
        }
        return in_array($publicMethodReference, $lowerLocalRefs, \true);
    }
}
