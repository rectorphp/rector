<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\UnusedPublic\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Collectors\PublicPropertyCollector;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Collectors\PublicPropertyFetchCollector;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Collectors\PublicStaticPropertyFetchCollector;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Configuration;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Enum\RuleTips;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Templates\TemplateMethodCallsProvider;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Utils\Arrays;
/**
 * @see \TomasVotruba\UnusedPublic\Tests\Rules\UnusedPublicPropertyRule\UnusedPublicPropertyRuleTest
 */
final class UnusedPublicPropertyRule implements Rule
{
    /**
     * @readonly
     */
    private Configuration $configuration;
    /**
     * @readonly
     */
    private TemplateMethodCallsProvider $templateMethodCallsProvider;
    /**
     * @api
     * @var string
     */
    public const ERROR_MESSAGE = 'Public property "%s::$%s" is never used';
    public function __construct(Configuration $configuration, TemplateMethodCallsProvider $templateMethodCallsProvider)
    {
        $this->configuration = $configuration;
        $this->templateMethodCallsProvider = $templateMethodCallsProvider;
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
        if (!$this->configuration->isUnusedPropertyEnabled()) {
            return [];
        }
        $publicPropertyCollector = $node->get(PublicPropertyCollector::class);
        $publicPropertyFetchCollector = $node->get(PublicPropertyFetchCollector::class);
        $publicStaticPropertyFetchCollector = $node->get(PublicStaticPropertyFetchCollector::class);
        $usedProperties = array_merge(Arrays::flatten($publicPropertyFetchCollector), Arrays::flatten($publicStaticPropertyFetchCollector));
        // property fetches used in templates, matched by bare property name
        $templatePropertyNames = array_merge($this->templateMethodCallsProvider->provideBladePropertyFetches(), $this->templateMethodCallsProvider->provideTwigMethodCalls());
        $ruleErrors = [];
        foreach ($publicPropertyCollector as $filePath => $declarationsGroups) {
            foreach ($declarationsGroups as $declarationGroup) {
                foreach ($declarationGroup as [$className, $propertyName, $line]) {
                    if ($this->isPropertyUsed($className, $propertyName, $usedProperties)) {
                        continue;
                    }
                    if (in_array($propertyName, $templatePropertyNames, \true)) {
                        continue;
                    }
                    /** @var string $propertyName */
                    $errorMessage = sprintf(self::ERROR_MESSAGE, $className, $propertyName);
                    $ruleErrors[] = RuleErrorBuilder::message($errorMessage)->file($filePath)->line($line)->tip(RuleTips::SOLUTION_MESSAGE)->identifier('public.property.unused')->build();
                }
            }
        }
        return $ruleErrors;
    }
    /**
     * @param mixed[] $usedProperties
     */
    private function isPropertyUsed(string $className, string $constantName, array $usedProperties): bool
    {
        $publicPropertyReference = $className . '::' . $constantName;
        return in_array($publicPropertyReference, $usedProperties, \true);
    }
}
