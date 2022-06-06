<?php

declare (strict_types=1);
namespace Rector\RectorGenerator;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\PrettyPrinter\Standard;
use Rector\RectorGenerator\NodeFactory\ConfigurationNodeFactory;
use Rector\RectorGenerator\NodeFactory\ConfigureClassMethodFactory;
use Rector\RectorGenerator\NodeFactory\NodeFactory;
use Rector\RectorGenerator\ValueObject\Placeholder;
use Rector\RectorGenerator\ValueObject\RectorRecipe;
final class TemplateVariablesFactory
{
    /**
     * @readonly
     * @var \PhpParser\PrettyPrinter\Standard
     */
    private $standard;
    /**
     * @readonly
     * @var \Rector\RectorGenerator\NodeFactory\ConfigurationNodeFactory
     */
    private $configurationNodeFactory;
    /**
     * @readonly
     * @var \Rector\RectorGenerator\NodeFactory\ConfigureClassMethodFactory
     */
    private $configureClassMethodFactory;
    /**
     * @readonly
     * @var \Rector\RectorGenerator\NodeFactory\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\PhpParser\PrettyPrinter\Standard $standard, \Rector\RectorGenerator\NodeFactory\ConfigurationNodeFactory $configurationNodeFactory, \Rector\RectorGenerator\NodeFactory\ConfigureClassMethodFactory $configureClassMethodFactory, \Rector\RectorGenerator\NodeFactory\NodeFactory $nodeFactory)
    {
        $this->standard = $standard;
        $this->configurationNodeFactory = $configurationNodeFactory;
        $this->configureClassMethodFactory = $configureClassMethodFactory;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @return array<string, mixed>
     */
    public function createFromRectorRecipe(\Rector\RectorGenerator\ValueObject\RectorRecipe $rectorRecipe) : array
    {
        $data = [\Rector\RectorGenerator\ValueObject\Placeholder::PACKAGE => $rectorRecipe->getPackage(), \Rector\RectorGenerator\ValueObject\Placeholder::CATEGORY => $rectorRecipe->getCategory(), \Rector\RectorGenerator\ValueObject\Placeholder::DESCRIPTION => $rectorRecipe->getDescription(), \Rector\RectorGenerator\ValueObject\Placeholder::NAME => $rectorRecipe->getName(), \Rector\RectorGenerator\ValueObject\Placeholder::CODE_BEFORE => \trim($rectorRecipe->getCodeBefore()) . \PHP_EOL, \Rector\RectorGenerator\ValueObject\Placeholder::CODE_BEFORE_EXAMPLE => $this->createCodeForDefinition($rectorRecipe->getCodeBefore()), \Rector\RectorGenerator\ValueObject\Placeholder::CODE_AFTER => \trim($rectorRecipe->getCodeAfter()) . \PHP_EOL, \Rector\RectorGenerator\ValueObject\Placeholder::CODE_AFTER_EXAMPLE => $this->createCodeForDefinition($rectorRecipe->getCodeAfter()), \Rector\RectorGenerator\ValueObject\Placeholder::RESOURCES => $this->createSourceDocBlock($rectorRecipe->getResources())];
        if ($rectorRecipe->getConfiguration() !== []) {
            $configurationData = $this->createConfigurationData($rectorRecipe);
            $data = \array_merge($data, $configurationData);
        }
        $data['__NodeTypesPhp__'] = $this->createNodeTypePhp($rectorRecipe);
        $data['__NodeTypesDoc__'] = '\\' . \implode('|\\', $rectorRecipe->getNodeTypes());
        return $data;
    }
    private function createCodeForDefinition(string $code) : string
    {
        if (\strpos($code, \PHP_EOL) !== \false) {
            // multi lines
            return \sprintf("<<<'CODE_SAMPLE'%s%s%sCODE_SAMPLE%s", \PHP_EOL, $code, \PHP_EOL, \PHP_EOL);
        }
        // single line
        return "'" . \str_replace("'", '"', $code) . "'";
    }
    /**
     * @param string[] $source
     */
    private function createSourceDocBlock(array $source) : string
    {
        if ($source === []) {
            return '';
        }
        $sourceAsString = '';
        foreach ($source as $singleSource) {
            $sourceAsString .= ' * @changelog ' . $singleSource . \PHP_EOL;
        }
        $sourceAsString .= ' *';
        return \rtrim($sourceAsString);
    }
    /**
     * @param array<string, mixed> $configuration
     */
    private function createRuleConfiguration(array $configuration) : string
    {
        $arrayItems = [];
        foreach ($configuration as $mainConfiguration) {
            $mainConfiguration = \PhpParser\BuilderHelpers::normalizeValue($mainConfiguration);
            $arrayItems[] = new \PhpParser\Node\Expr\ArrayItem($mainConfiguration);
        }
        $array = new \PhpParser\Node\Expr\Array_($arrayItems);
        return $this->standard->prettyPrintExpr($array);
    }
    /**
     * @param array<string, mixed> $ruleConfiguration
     */
    private function createConfigurationProperty(array $ruleConfiguration) : string
    {
        $properties = $this->configurationNodeFactory->createProperties($ruleConfiguration);
        return $this->standard->prettyPrint($properties);
    }
    /**
     * @param array<string, mixed> $ruleConfiguration
     */
    private function createConfigureClassMethod(array $ruleConfiguration) : string
    {
        $classMethod = $this->configureClassMethodFactory->create($ruleConfiguration);
        return $this->standard->prettyPrint([$classMethod]);
    }
    private function createNodeTypePhp(\Rector\RectorGenerator\ValueObject\RectorRecipe $rectorRecipe) : string
    {
        $referencingClassConsts = [];
        foreach ($rectorRecipe->getNodeTypes() as $nodeType) {
            $referencingClassConsts[] = $this->nodeFactory->createClassConstReference($nodeType);
        }
        $array = $this->nodeFactory->createArray($referencingClassConsts);
        return $this->standard->prettyPrintExpr($array);
    }
    /**
     * @return array<string, mixed>
     */
    private function createConfigurationData(\Rector\RectorGenerator\ValueObject\RectorRecipe $rectorRecipe) : array
    {
        $configurationData = [];
        $configurationData['__TestRuleConfiguration__'] = $this->createRuleConfiguration($rectorRecipe->getConfiguration());
        $configurationData['__RuleConfiguration__'] = $this->createRuleConfiguration($rectorRecipe->getConfiguration());
        $configurationData['__ConfigurationProperties__'] = $this->createConfigurationProperty($rectorRecipe->getConfiguration());
        $configurationData['__ConfigureClassMethod__'] = $this->createConfigureClassMethod($rectorRecipe->getConfiguration());
        $configurationData['__MainConfiguration__'] = $this->createMainConfiguration($rectorRecipe->getConfiguration());
        return $configurationData;
    }
    /**
     * @param array<string, mixed> $ruleConfiguration
     */
    private function createMainConfiguration(array $ruleConfiguration) : string
    {
        $firstItem = \array_pop($ruleConfiguration);
        $valueExpr = \PhpParser\BuilderHelpers::normalizeValue($firstItem);
        return $this->standard->prettyPrintExpr($valueExpr);
    }
}
