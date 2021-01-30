<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Rector\StaticCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#writing-pdf
 *
 * @see \Rector\PHPOffice\Tests\Rector\StaticCall\ChangePdfWriterRector\ChangePdfWriterRectorTest
 */
final class ChangePdfWriterRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change init of PDF writer', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        \PHPExcel_Settings::setPdfRendererName(PHPExcel_Settings::PDF_RENDERER_MPDF);
        \PHPExcel_Settings::setPdfRenderer($somePath);
        $writer = \PHPExcel_IOFactory::createWriter($spreadsheet, 'PDF');
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isStaticCallNamed($node, 'PHPExcel_Settings', 'setPdfRendererName')) {
            $this->removeNode($node);
            return null;
        }

        if ($this->isStaticCallNamed($node, 'PHPExcel_Settings', 'setPdfRenderer')) {
            $this->removeNode($node);
            return null;
        }

        if ($this->isStaticCallNamed($node, 'PHPExcel_IOFactory', 'createWriter')) {
            if (! isset($node->args[1])) {
                return null;
            }

            $secondArgValue = $this->valueResolver->getValue($node->args[1]->value);
            if (Strings::match($secondArgValue, '#pdf#i')) {
                return new New_(new FullyQualified('PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf'), [$node->args[0]]);
            }
        }

        return $node;
    }
}
