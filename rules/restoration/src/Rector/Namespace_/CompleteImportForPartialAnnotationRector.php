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
use Rector\Core\PhpParser\Builder\UseBuilder;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Restoration\ValueObject\UseWithAlias;

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
     * @var UseWithAlias[]
     */
    private $useImportsToRestore = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('In case you have accidentally removed use imports but code still contains partial use statements, this will save you', [
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
                    self::USE_IMPORTS_TO_RESTORE => [new UseWithAlias('Doctrine\ORM\Mapping', 'ORM')],
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
        /** @var Class_|null $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf((array) $node->stmts, Class_::class);
        if ($class === null) {
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
     * @param UseWithAlias[][] $configuration
     */
    public function configure(array $configuration): void
    {
        $default = [
            new UseWithAlias('Doctrine\ORM\Mapping', 'ORM'),
            new UseWithAlias('Symfony\Component\Validator\Constraints', 'Assert'),
            new UseWithAlias('JMS\Serializer\Annotation', 'Serializer'),
        ];

        $this->useImportsToRestore = array_merge($configuration[self::USE_IMPORTS_TO_RESTORE] ?? [], $default);
    }

    private function addImportToNamespaceIfMissing(Namespace_ $namespace, UseWithAlias $useWithAlias): Namespace_
    {
        foreach ($namespace->stmts as $stmt) {
            if (! $stmt instanceof Use_) {
                continue;
            }

            $useUse = $stmt->uses[0];

            // already there
            if ($this->isName(
                $useUse->name,
                $useWithAlias->getUse()
            ) && (string) $useUse->alias === $useWithAlias->getAlias()) {
                return $namespace;
            }
        }

        return $this->addImportToNamespace($namespace, $useWithAlias);
    }

    private function addImportToNamespace(Namespace_ $namespace, UseWithAlias $useWithAlias): Namespace_
    {
        $useBuilder = new UseBuilder($useWithAlias->getUse());
        if ($useWithAlias->getAlias() !== '') {
            $useBuilder->as($useWithAlias->getAlias());
        }

        /** @var Stmt $use */
        $use = $useBuilder->getNode();

        $namespace->stmts = array_merge([$use], (array) $namespace->stmts);

        return $namespace;
    }
}
