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
            ->notPath('#/FileWithoutNamespace/PseudoNamespaceToNamespaceRector/Fixture/fixture3\.php\.inc$#')
            ->notPath('#bootstrap_names\.php\.inc#')
            ->notPath('#keep_anonymous_classes\.php\.inc#')
            ->notPath('#skip_different_order\.php\.inc#')
            ->notPath('#extended_parent\.php\.inc#')
            ->notPath('#trait_name\.php\.inc#')
            ->notPath('#normalize_file\.php\.inc#')
            ->notPath('#wrong_namespace\.php\.inc#')
            ->notName('#_\.php\.inc$#')
            ->in(__DIR__ . '/../../../../tests')
            ->in(__DIR__ . '/../../../../packages')
            ->in(__DIR__ . '/../../../../rules/architecture')
            ->in(__DIR__ . '/../../../../rules/autodiscovery')
            ->in(__DIR__ . '/../../../../rules/cakephp')
            ->in(__DIR__ . '/../../../../rules/carbon')
            ->in(__DIR__ . '/../../../../rules/code-quality')
            ->in(__DIR__ . '/../../../../rules/coding-style')
            ->in(__DIR__ . '/../../../../rules/dead-code')
            ->in(__DIR__ . '/../../../../rules/dead-doc-block')
            ->in(__DIR__ . '/../../../../rules/defluent')
            ->in(__DIR__ . '/../../../../rules/doctrine')
            ->in(__DIR__ . '/../../../../rules/doctrine-code-quality')
            ->in(__DIR__ . '/../../../../rules/doctrine-gedmo-to-knplabs')
            ->in(__DIR__ . '/../../../../rules/downgrade')
            ->in(__DIR__ . '/../../../../rules/downgrade-php70')
            ->in(__DIR__ . '/../../../../rules/downgrade-php71')
            ->in(__DIR__ . '/../../../../rules/downgrade-php72')
            ->in(__DIR__ . '/../../../../rules/downgrade-php73')
            ->in(__DIR__ . '/../../../../rules/downgrade-php74')
            ->in(__DIR__ . '/../../../../rules/downgrade-php80')
            ->in(__DIR__ . '/../../../../rules/early-return')
            ->in(__DIR__ . '/../../../../rules/generic')
            ->in(__DIR__ . '/../../../../rules/jms')
            ->in(__DIR__ . '/../../../../rules/laravel')
            ->in(__DIR__ . '/../../../../rules/legacy')
            ->in(__DIR__ . '/../../../../rules/magic-disclosure')
            ->in(__DIR__ . '/../../../../rules/mockery-to-prophecy')
            ->in(__DIR__ . '/../../../../rules/mockista-to-mockery')
            ->in(__DIR__ . '/../../../../rules/mysql-to-mysqli')
            ->in(__DIR__ . '/../../../../rules/naming')
            ->in(__DIR__ . '/../../../../rules/nette')
            ->in(__DIR__ . '/../../../../rules/phpstan')
            ->in(__DIR__ . '/../../../../rules/phpunit')
            ->in(__DIR__ . '/../../../../rules/phpunit-symfony')
            ->in(__DIR__ . '/../../../../rules/polyfill')
            ->in(__DIR__ . '/../../../../rules/privatization')
            ->in(__DIR__ . '/../../../../rules/psr4')
            ->in(__DIR__ . '/../../../../rules/removing-static')
            ->in(__DIR__ . '/../../../../rules/renaming')
            ->in(__DIR__ . '/../../../../rules/restoration')
            ->in(__DIR__ . '/../../../../rules/sensio')
            ->in(__DIR__ . '/../../../../rules/solid')
            ->in(__DIR__ . '/../../../../rules/strict-code-quality')
            ->in(__DIR__ . '/../../../../rules/symfony')
            ->in(__DIR__ . '/../../../../rules/symfony-code-quality')
            ->in(__DIR__ . '/../../../../rules/symfony-php-config')
            ->in(__DIR__ . '/../../../../rules/symfony2')
            ->in(__DIR__ . '/../../../../rules/symfony3')
            ->in(__DIR__ . '/../../../../rules/symfony4')
            ->in(__DIR__ . '/../../../../rules/symfony5')
            ->in(__DIR__ . '/../../../../rules/transform')
            ->in(__DIR__ . '/../../../../rules/twig')
            ->in(__DIR__ . '/../../../../rules/type-declaration');

        return $this->finderSanitizer->sanitize($finder);
    }
}
