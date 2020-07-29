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
use Rector\RectorGenerator\ValueObject\Configuration;

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
    public function createFromConfiguration(Configuration $configuration): array
    {
        $data = [
            self::VARIABLE_PACKAGE => $configuration->getPackage(),
            self::VARIABLE_PACKAGE_LOWERCASE => $configuration->getPackageDirectory(),
            '__Category__' => $configuration->getCategory(),
            '__Description__' => $configuration->getDescription(),
            '__Name__' => $configuration->getName(),
            '__CodeBefore__' => trim($configuration->getCodeBefore()) . PHP_EOL,
            '__CodeBeforeExample__' => $this->createCodeForDefinition($configuration->getCodeBefore()),
            '__CodeAfter__' => trim($configuration->getCodeAfter()) . PHP_EOL,
            '__CodeAfterExample__' => $this->createCodeForDefinition($configuration->getCodeAfter()),
            '__Source__' => $this->createSourceDocBlock($configuration->getSource()),
        ];

        $rectorClass = $this->templateFactory->create(ConfigFilesystem::RECTOR_FQN_NAME_PATTERN, $data);
        $data['__RectorClass_'] = $rectorClass;

        if ($configuration->getRuleConfiguration() !== []) {
            $data['__TestRuleConfiguration__'] = $this->createRuleConfiguration(
                $data['__RectorClass_'],
                $configuration->getRuleConfiguration()
            );
            $data['__RuleConfiguration__'] = $this->createRuleConfiguration(
                self::SELF,
                $configuration->getRuleConfiguration()
            );

            $data['__ConfigurationProperties__'] = $this->createConfigurationProperty(
                $configuration->getRuleConfiguration()
            );

            $data['__ConfigurationConstants__'] = $this->createConfigurationConstants(
                $configuration->getRuleConfiguration()
            );

            $data['__ConfigureClassMethod__'] = $this->createConfigureClassMethod(
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

        $data['__NodeTypesPhp__'] = $this->createNodeTypePhp($configuration);
        $data['__NodeTypesDoc__'] = '\\' . implode('|\\', $configuration->getNodeTypes());

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
    private function createConfigureClassMethod(array $ruleConfiguration): string
    {
        $classMethod = $this->configurationNodeFactory->createConfigureClassMethod($ruleConfiguration);
        return $this->betterStandardPrinter->print($classMethod);
    }

    /**
     * @param array<string, mixed> $ruleConfiguration
     */
    private function createConfigurationConstants(array $ruleConfiguration): string
    {
        $configurationConstants = $this->configurationNodeFactory->createConfigurationConstants($ruleConfiguration);
        return $this->betterStandardPrinter->print($configurationConstants);
    }
}
