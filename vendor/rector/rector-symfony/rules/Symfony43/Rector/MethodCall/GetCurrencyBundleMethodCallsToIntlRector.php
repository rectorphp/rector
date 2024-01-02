<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony43\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\Symfony\ValueObject\IntlBundleClassToNewClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/blog/new-in-symfony-4-3-simpler-access-to-intl-data
 * @changelog https://github.com/symfony/symfony/pull/28846
 *
 * @see \Rector\Symfony\Tests\Symfony43\Rector\MethodCall\GetCurrencyBundleMethodCallsToIntlRector\GetCurrencyBundleMethodCallsToIntlRectorTest
 */
final class GetCurrencyBundleMethodCallsToIntlRector extends AbstractRector
{
    /**
     * @var IntlBundleClassToNewClass[]
     */
    private $intlBundleClassesToNewClasses = [];
    public function __construct()
    {
        $this->intlBundleClassesToNewClasses[] = new IntlBundleClassToNewClass('Symfony\\Component\\Intl\\ResourceBundle\\LanguageBundleInterface', 'Symfony\\Component\\Intl\\Languages', ['getLanguageNames' => 'getNames', 'getLanguageName' => 'getName']);
        $this->intlBundleClassesToNewClasses[] = new IntlBundleClassToNewClass('Symfony\\Component\\Intl\\ResourceBundle\\RegionBundleInterface', 'Symfony\\Component\\Intl\\Currencies', ['getCountryNames' => 'getNames', 'getCountryName' => 'getName']);
        $this->intlBundleClassesToNewClasses[] = new IntlBundleClassToNewClass('Symfony\\Component\\Intl\\ResourceBundle\\CurrencyBundleInterface', 'Symfony\\Component\\Intl\\Currencies', ['getCurrencyNames' => 'getNames', 'getCurrencyName' => 'getName', 'getCurrencySymbol' => 'getSymbol', 'getFractionDigits' => 'getFractionDigits']);
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Intl static bundle method were changed to direct static calls', [new CodeSample(<<<'CODE_SAMPLE'
$currencyBundle = \Symfony\Component\Intl\Intl::getCurrencyBundle();

$currencyNames = $currencyBundle->getCurrencyNames();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$currencyNames = \Symfony\Component\Intl\Currencies::getNames();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?StaticCall
    {
        foreach ($this->intlBundleClassesToNewClasses as $intlBundleClassToNewClass) {
            if (!$this->isObjectType($node->var, new ObjectType($intlBundleClassToNewClass->getOldClass()))) {
                continue;
            }
            foreach ($intlBundleClassToNewClass->getOldToNewMethods() as $oldMethodName => $newMethodName) {
                if (!$this->isName($node->name, $oldMethodName)) {
                    continue;
                }
                $currenciesFullyQualified = new FullyQualified($intlBundleClassToNewClass->getNewClass());
                return new StaticCall($currenciesFullyQualified, $newMethodName);
            }
        }
        return null;
    }
}
