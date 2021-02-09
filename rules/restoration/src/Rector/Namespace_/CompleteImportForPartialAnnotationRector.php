<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\Namespace_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Restoration\ValueObject\CompleteImportForPartialAnnotation;
use Symplify\Astral\ValueObject\NodeBuilder\UseBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Restoration\Tests\Rector\Namespace_\CompleteImportForPartialAnnotationRector\CompleteImportForPartialAnnotationRectorTest
 */
final class CompleteImportForPartialAnnotationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const USE_IMPORTS_TO_RESTORE = '$useImportsToRestore';

    /**
     * @var CompleteImportForPartialAnnotation[]
     */
    private $useImportsToRestore = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('In case you have accidentally removed use imports but code still contains partial use statements, this will save you', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @ORM\Id
     */
    public $id;
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SomeClass
{
    /**
     * @ORM\Id
     */
    public $id;
}
CODE_SAMPLE
                ,
                [
                    self::USE_IMPORTS_TO_RESTORE => [
                        new CompleteImportForPartialAnnotation('Doctrine\ORM\Mapping', 'ORM'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Namespace_::class];
    }

    /**
     * @param Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $class = $this->betterNodeFinder->findFirstInstanceOf($node->stmts, Class_::class);
        if (! $class instanceof Class_) {
            return null;
        }

        foreach ($this->useImportsToRestore as $useImportToRestore) {
            $annotationToSeek = '#\*\s+\@' . $useImportToRestore->getAlias() . '#';
            if (! Strings::match($this->print($class), $annotationToSeek)) {
                continue;
            }

            $node = $this->addImportToNamespaceIfMissing($node, $useImportToRestore);
        }

        return $node;
    }

    /**
     * @param CompleteImportForPartialAnnotation[][] $configuration
     */
    public function configure(array $configuration): void
    {
        $default = [
            new CompleteImportForPartialAnnotation('Doctrine\ORM\Mapping', 'ORM'),
            new CompleteImportForPartialAnnotation('Symfony\Component\Validator\Constraints', 'Assert'),
            new CompleteImportForPartialAnnotation('JMS\Serializer\Annotation', 'Serializer'),
        ];

        $this->useImportsToRestore = array_merge($configuration[self::USE_IMPORTS_TO_RESTORE] ?? [], $default);
    }

    private function addImportToNamespaceIfMissing(
        Namespace_ $namespace,
        CompleteImportForPartialAnnotation $completeImportForPartialAnnotation
    ): Namespace_ {
        foreach ($namespace->stmts as $stmt) {
            if (! $stmt instanceof Use_) {
                continue;
            }

            $useUse = $stmt->uses[0];

            // already there
            if ($this->isName(
                $useUse->name,
                $completeImportForPartialAnnotation->getUse()
            ) && (string) $useUse->alias === $completeImportForPartialAnnotation->getAlias()) {
                return $namespace;
            }
        }

        return $this->addImportToNamespace($namespace, $completeImportForPartialAnnotation);
    }

    private function addImportToNamespace(
        Namespace_ $namespace,
        CompleteImportForPartialAnnotation $completeImportForPartialAnnotation
    ): Namespace_ {
        $useBuilder = new UseBuilder($completeImportForPartialAnnotation->getUse());
        if ($completeImportForPartialAnnotation->getAlias() !== '') {
            $useBuilder->as($completeImportForPartialAnnotation->getAlias());
        }

        /** @var Stmt $use */
        $use = $useBuilder->getNode();

        $namespace->stmts = array_merge([$use], $namespace->stmts);

        return $namespace;
    }
}
