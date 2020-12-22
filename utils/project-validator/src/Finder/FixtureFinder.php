<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Finder;

use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FixtureFinder
{
    /**
     * @var FinderSanitizer
     */
    private $finderSanitizer;

    public function __construct(FinderSanitizer $finderSanitizer)
    {
        $this->finderSanitizer = $finderSanitizer;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function findFixtureFileInfos(): array
    {
        $finder = new Finder();
        $finder = $finder->files()
            ->name('#\.php\.inc$#')
            ->notName('#empty_file\.php\.inc$#')
            ->path('#/Fixture/#')
            ->notPath('#/blade-template/#')
            ->notPath('#/Name/RenameClassRector/#')
            ->notPath('#/Namespace_/RenameNamespaceRector/#')
            ->notPath('#/TemplateAnnotationToThisRenderRector/#')
            ->notPath('#/ParamTypeDeclarationRector/#')
            ->notPath('#/ReturnTypeDeclarationRector/#')
            ->notPath('#/PhpSpecToPHPUnitRector/#')
            ->notPath('#/FileWithoutNamespace/PseudoNamespaceToNamespaceRector/Fixture/fixture3\.php\.inc$#')
            ->notPath('#/SwapClassMethodArgumentsRector/Fixture/fixture\.php\.inc$#')
            ->notPath('#bootstrap_names\.php\.inc$#')
            ->notPath('#keep_anonymous_classes\.php\.inc$#')
            ->notPath('#skip_different_order\.php\.inc$#')
            ->notPath('#extended_parent\.php\.inc$#')
            ->notPath('#trait_name\.php\.inc$#')
            ->notPath('#normalize_file\.php\.inc$#')
            ->notPath('#wrong_namespace\.php\.inc$#')
            ->notPath('#stringy_calls\.php\.inc$#')
            ->notPath('#delegating(_\d)?\.php\.inc$#')
            ->notPath('#keep_annotated\.php\.inc$#')
            ->notPath('#double_same_variable\.php\.inc$#')
            ->notName('#_\.php\.inc$#')
            ->in(__DIR__ . '/../../../../tests')
            ->in(__DIR__ . '/../../../../packages')
            ->in(__DIR__ . '/../../../../rules');

        return $this->finderSanitizer->sanitize($finder);
    }
}
