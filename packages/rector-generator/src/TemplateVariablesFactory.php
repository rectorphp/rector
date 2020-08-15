<?php

declare(strict_types=1);

namespace Rector\RectorGenerator;

use Nette\Utils\Strings;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\RectorGenerator\Config\ConfigFilesystem;
use Rector\RectorGenerator\NodeFactory\ConfigurationNodeFactory;
use Rector\RectorGenerator\ValueObject\RectorRecipeConfiguration;

final class TemplateVariablesFactory
{
    /**
     * @var string
     */
    private const SELF = 'self';

    /**
     * @var string
     */
    private const VARIABLE_PACKAGE = '__Package__';

    /**
     * @var string
     */
    private const VARIABLE_PACKAGE_LOWERCASE = '__package__';

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var ConfigurationNodeFactory
     */
    private $configurationNodeFactory;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        ConfigurationNodeFactory $configurationNodeFactory,
        NodeFactory $nodeFactory,
        TemplateFactory $templateFactory
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeFactory = $nodeFactory;
        $this->configurationNodeFactory = $configurationNodeFactory;
        $this->templateFactory = $templateFactory;
    }

    /**
     * @return string[]
     */
    public function createFromConfiguration(RectorRecipeConfiguration $rectorRecipeConfiguration): array
    {
        $data = [
            self::VARIABLE_PACKAGE => $rectorRecipeConfiguration->getPackage(),
            self::VARIABLE_PACKAGE_LOWERCASE => $rectorRecipeConfiguration->getPackageDirectory(),
            '__Category__' => $rectorRecipeConfiguration->getCategory(),
            '__Description__' => $rectorRecipeConfiguration->getDescription(),
            '__Name__' => $rectorRecipeConfiguration->getName(),
            '__CodeBefore__' => trim($rectorRecipeConfiguration->getCodeBefore()) . PHP_EOL,
            '__CodeBeforeExample__' => $this->createCodeForDefinition($rectorRecipeConfiguration->getCodeBefore()),
            '__CodeAfter__' => trim($rectorRecipeConfiguration->getCodeAfter()) . PHP_EOL,
            '__CodeAfterExample__' => $this->createCodeForDefinition($rectorRecipeConfiguration->getCodeAfter()),
            '__Source__' => $this->createSourceDocBlock($rectorRecipeConfiguration->getSource()),
        ];

        $rectorClass = $this->templateFactory->create(ConfigFilesystem::RECTOR_FQN_NAME_PATTERN, $data);

        if ($rectorRecipeConfiguration->getRuleConfiguration() !== []) {
            $data['__TestRuleConfiguration__'] = $this->createRuleConfiguration(
                $rectorClass,
                $rectorRecipeConfiguration->getRuleConfiguration()
            );
            $data['__RuleConfiguration__'] = $this->createRuleConfiguration(
                self::SELF,
                $rectorRecipeConfiguration->getRuleConfiguration()
            );

            $data['__ConfigurationProperties__'] = $this->createConfigurationProperty(
                $rectorRecipeConfiguration->getRuleConfiguration()
            );

            $data['__ConfigurationConstants__'] = $this->createConfigurationConstants(
                $rectorRecipeConfiguration->getRuleConfiguration()
            );

            $data['__ConfigureClassMethod__'] = $this->createConfigureClassMethod(
                $rectorRecipeConfiguration->getRuleConfiguration()
            );
        }

        if ($rectorRecipeConfiguration->getExtraFileContent() !== null && $rectorRecipeConfiguration->getExtraFileName() !== null) {
            $data['__ExtraFileName__'] = $rectorRecipeConfiguration->getExtraFileName();
            $data['__ExtraFileContent__'] = trim($rectorRecipeConfiguration->getExtraFileContent()) . PHP_EOL;
            $data['__ExtraFileContentExample__'] = $this->createCodeForDefinition(
                $rectorRecipeConfiguration->getExtraFileContent()
            );
        }

        $data['__NodeTypesPhp__'] = $this->createNodeTypePhp($rectorRecipeConfiguration);
        $data['__NodeTypesDoc__'] = '\\' . implode('|\\', $rectorRecipeConfiguration->getNodeTypes());

        return $data;
    }

    private function createCodeForDefinition(string $code): string
    {
        if (Strings::contains($code, PHP_EOL)) {
            // multi lines
            return sprintf("<<<'PHP'%s%s%sPHP%s", PHP_EOL, $code, PHP_EOL, PHP_EOL);
        }

        // single line
        return "'" . str_replace("'", '"', $code) . "'";
    }

    /**
     * @param string[] $source
     */
    private function createSourceDocBlock(array $source): string
    {
        if ($source === []) {
            return '';
        }

        $sourceAsString = '';
        foreach ($source as $singleSource) {
            $sourceAsString .= ' * @see ' . $singleSource . PHP_EOL;
        }

        $sourceAsString .= ' *';

        return rtrim($sourceAsString);
    }

    /**
     * @param mixed[] $configuration
     */
    private function createRuleConfiguration(string $rectorClass, array $configuration): string
    {
        $arrayItems = [];
        foreach ($configuration as $constantName => $variableConfiguration) {
            if ($rectorClass === self::SELF) {
                $class = new Name(self::SELF);
            } else {
                $class = new FullyQualified($rectorClass);
            }
            $classConstFetch = new ClassConstFetch($class, $constantName);
            $arrayItems[] = new ArrayItem($this->nodeFactory->createArray($variableConfiguration), $classConstFetch);
        }

        $array = new Array_($arrayItems);
        return $this->betterStandardPrinter->print($array);
    }

    /**
     * @param array<string, mixed> $ruleConfiguration
     */
    private function createConfigurationProperty(array $ruleConfiguration): string
    {
        $properties = $this->configurationNodeFactory->createProperties($ruleConfiguration);
        return $this->betterStandardPrinter->print($properties);
    }

    /**
     * @param array<string, mixed> $ruleConfiguration
     */
    private function createConfigurationConstants(array $ruleConfiguration): string
    {
        $configurationConstants = $this->configurationNodeFactory->createConfigurationConstants($ruleConfiguration);
        return $this->betterStandardPrinter->print($configurationConstants);
    }

    /**
     * @param array<string, mixed> $ruleConfiguration
     */
    private function createConfigureClassMethod(array $ruleConfiguration): string
    {
        $classMethod = $this->configurationNodeFactory->createConfigureClassMethod($ruleConfiguration);
        return $this->betterStandardPrinter->print($classMethod);
    }

    private function createNodeTypePhp(RectorRecipeConfiguration $rectorRecipeConfiguration): string
    {
        $referencingClassConsts = [];
        foreach ($rectorRecipeConfiguration->getNodeTypes() as $nodeType) {
            $referencingClassConsts[] = $this->nodeFactory->createClassConstReference($nodeType);
        }

        $array = $this->nodeFactory->createArray($referencingClassConsts);
        return $this->betterStandardPrinter->print($array);
    }
}
