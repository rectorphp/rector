<?php

declare(strict_types=1);

namespace Rector\RectorGenerator;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
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

    public function __construct(BetterStandardPrinter $betterStandardPrinter, NodeFactory $nodeFactory)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeFactory = $nodeFactory;
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

    private function createConfigurationProperty(array $ruleConfiguration): string
    {
        $properties = [];
        foreach (array_keys($ruleConfiguration) as $variable) {
            $variable = ltrim($variable, '$');
            $type = new ArrayType(new MixedType(), new MixedType());
            $properties[] = $this->nodeFactory->createPrivatePropertyFromNameAndType($variable, $type);
        }

        return $this->betterStandardPrinter->print($properties);
    }

    private function createConfigurationConstructor(array $ruleConfiguration): string
    {
        $classMethod = $this->nodeFactory->createPublicMethod('__construct');

        $assigns = [];
        $params = [];

        foreach ($ruleConfiguration as $variable => $values) {
            $variable = ltrim($variable, '$');
            $assign = $this->nodeFactory->createPropertyAssignment($variable);
            $assigns[] = new Expression($assign);

            if (is_array($values)) {
                $type = new ArrayType(new MixedType(), new MixedType());
            } elseif (is_string($values)) {
                $type = new StringType();
            } else {
                throw new NotImplementedYetException();
            }

            $params[] = $this->nodeFactory->createParamFromNameAndType($variable, $type);
        }

        $classMethod->params = $params;
        $classMethod->stmts = $assigns;

        return $this->betterStandardPrinter->print($classMethod);
    }
}
