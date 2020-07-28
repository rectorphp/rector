<?php

declare(strict_types=1);

namespace Rector\RectorGenerator;

use Nette\Utils\Strings;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\RectorGenerator\NodeFactory\ConfigurationNodeFactory;
use Rector\RectorGenerator\ValueObject\Configuration;

final class TemplateVariablesFactory
{
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

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        ConfigurationNodeFactory $configurationNodeFactory,
        NodeFactory $nodeFactory
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeFactory = $nodeFactory;
        $this->configurationNodeFactory = $configurationNodeFactory;
    }

    /**
     * @return mixed[]
     */
    public function createFromConfiguration(Configuration $configuration): array
    {
        $data = [
            '__Package__' => $configuration->getPackage(),
            '__package__' => $configuration->getPackageDirectory(),
            '__Category__' => $configuration->getCategory(),
            '__Description__' => $configuration->getDescription(),
            '__Name__' => $configuration->getName(),
            '__CodeBefore__' => trim($configuration->getCodeBefore()) . PHP_EOL,
            '__CodeBeforeExample__' => $this->createCodeForDefinition($configuration->getCodeBefore()),
            '__CodeAfter__' => trim($configuration->getCodeAfter()) . PHP_EOL,
            '__CodeAfterExample__' => $this->createCodeForDefinition($configuration->getCodeAfter()),
            '__Source__' => $this->createSourceDocBlock($configuration->getSource()),
        ];

        if ($configuration->getRuleConfiguration() !== []) {
            $data['__RuleConfiguration__'] = $this->createRuleConfiguration($configuration->getRuleConfiguration());
            $data['__ConfigurationProperty__'] = $this->createConfigurationProperty(
                $configuration->getRuleConfiguration()
            );

            $data['__ConfigurationConstructor__'] = $this->createConfigurationConstructor(
                $configuration->getRuleConfiguration()
            );
        }

        if ($configuration->getExtraFileContent() !== null && $configuration->getExtraFileName() !== null) {
            $data['__ExtraFileName__'] = $configuration->getExtraFileName();
            $data['__ExtraFileContent__'] = trim($configuration->getExtraFileContent()) . PHP_EOL;
            $data['__ExtraFileContentExample__'] = $this->createCodeForDefinition(
                $configuration->getExtraFileContent()
            );
        }

        $data['__NodeTypes_Php__'] = $this->createNodeTypePhp($configuration);
        $data['__NodeTypes_Doc__'] = '\\' . implode('|\\', $configuration->getNodeTypes());

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

    private function createNodeTypePhp(Configuration $configuration): string
    {
        $referencingClassConsts = [];
        foreach ($configuration->getNodeTypes() as $nodeType) {
            $referencingClassConsts[] = $this->nodeFactory->createClassConstReference($nodeType);
        }

        $array = $this->nodeFactory->createArray($referencingClassConsts);
        return $this->betterStandardPrinter->print($array);
    }

    /**
     * @param mixed[] $configuration
     */
    private function createRuleConfiguration(array $configuration): string
    {
        $array = $this->nodeFactory->createArray($configuration);
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
    private function createConfigurationConstructor(array $ruleConfiguration): string
    {
        $classMethod = $this->configurationNodeFactory->createConstructorClassMethod($ruleConfiguration);
        return $this->betterStandardPrinter->print($classMethod);
    }
}
