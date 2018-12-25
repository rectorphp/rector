<?php declare(strict_types=1);

namespace Rector\ContributorTools;

use Nette\Utils\Strings;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use Rector\ContributorTools\Configuration\Configuration;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use function Safe\sprintf;

final class TemplateVariablesFactory
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @return mixed[]
     */
    public function createFromConfiguration(Configuration $configuration): array
    {
        $data = [
            '_Package_' => $configuration->getPackage(),
            '_Category_' => $configuration->getCategory(),
            '_Description_' => $configuration->getDescription(),
            '_Name_' => $configuration->getName(),
            '_CodeBefore_' => trim($configuration->getCodeBefore()) . PHP_EOL,
            '_CodeBeforeExample_' => $this->createCodeForDefinition($configuration->getCodeBefore()),
            '_CodeAfter_' => trim($configuration->getCodeAfter()) . PHP_EOL,
            '_CodeAfterExample_' => $this->createCodeForDefinition($configuration->getCodeAfter()),
            '_Source_' => $this->createSourceDocBlock($configuration->getSource()),
        ];

        $data['_NodeTypes_Php_'] = $this->createNodeTypePhp($configuration);
        $data['_NodeTypes_Doc_'] = '\\' . implode('|\\', $configuration->getNodeTypes());

        return $data;
    }

    private function createCodeForDefinition(string $code): string
    {
        if (Strings::contains($code, PHP_EOL)) {
            // multi lines
            return sprintf("<<<'CODE_SAMPLE'%s%s%sCODE_SAMPLE%s", PHP_EOL, $code, PHP_EOL, PHP_EOL);
        }

        // single line
        return "'" . str_replace("'", '"', $code) . "'";
    }

    /**
     * @param string[] $source
     */
    private function createSourceDocBlock(array $source): string
    {
        if (! $source) {
            return '';
        }

        $sourceDocBlock = <<<'CODE_SAMPLE'
/**
%s
 */
CODE_SAMPLE;

        $sourceAsString = '';
        foreach ($source as $singleSource) {
            $sourceAsString .= ' * @see ' . $singleSource . PHP_EOL;
        }

        return sprintf($sourceDocBlock, rtrim($sourceAsString));
    }

    private function createNodeTypePhp(Configuration $configuration): string
    {
        $arrayNodes = [];
        foreach ($configuration->getNodeTypes() as $nodeType) {
            $arrayNodes[] = new ArrayItem(new ClassConstFetch(new FullyQualified($nodeType), 'class'));
        }

        return $this->betterStandardPrinter->print(new Array_($arrayNodes));
    }
}
