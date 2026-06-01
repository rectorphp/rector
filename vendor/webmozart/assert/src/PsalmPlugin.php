<?php

declare (strict_types=1);
namespace RectorPrefix202606\Webmozart\Assert;

use RectorPrefix202606\Psalm\Internal\Analyzer\Statements\Expression\ExpressionIdentifier;
use RectorPrefix202606\Psalm\Plugin\EventHandler\AfterMethodCallAnalysisInterface;
use RectorPrefix202606\Psalm\Plugin\EventHandler\Event\AfterMethodCallAnalysisEvent;
use RectorPrefix202606\Psalm\Plugin\PluginEntryPointInterface;
use RectorPrefix202606\Psalm\PluginRegistrationSocket;
use SimpleXMLElement;
final class PsalmPlugin implements PluginEntryPointInterface, AfterMethodCallAnalysisInterface
{
    public function __invoke(PluginRegistrationSocket $registration, ?SimpleXMLElement $config = null): void
    {
        $registration->registerHooksFromClass(self::class);
    }
    public static function afterMethodCallAnalysis(AfterMethodCallAnalysisEvent $event): void
    {
        [$class, $method] = explode('::', $event->getAppearingMethodId());
        if ($class !== Assert::class) {
            return;
        }
        if (!isset(HasAssert::HAS_ASSERT[$method])) {
            return;
        }
        $firstArg = $event->getExpr()->getArgs()[0] ?? null;
        if ($firstArg === null) {
            return;
        }
        $varId = ExpressionIdentifier::getExtendedVarId($firstArg->value, $event->getContext()->self, $event->getStatementsSource());
        if ($varId === null || !isset($event->getContext()->vars_in_scope[$varId])) {
            return;
        }
        $candidateType = $event->getContext()->vars_in_scope[$varId];
        $event->setReturnTypeCandidate($candidateType);
    }
}
