<?php

declare (strict_types=1);
namespace Rector\Carbon\NodeFactory;

use RectorPrefix202506\Nette\Utils\Strings;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
final class CarbonCallFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/LLMrFw/1
     */
    private const PLUS_MINUS_COUNT_REGEX = '#(?<operator>\\+|-)(\\s+)?(?<count>\\d+)(\\s+)?(?<unit>seconds|second|sec|minutes|minute|min|hours|hour|days|day|weeks|week|months|month|years|year)#';
    /**
     * @var string
     * @see https://regex101.com/r/IhxHTO/1
     */
    private const STATIC_DATE_REGEX = '#now|yesterday|today|tomorrow#';
    /**
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall
     */
    public function createFromDateTimeString(FullyQualified $carbonFullyQualified, String_ $string)
    {
        $carbonCall = $this->createStaticCall($carbonFullyQualified, $string);
        $string->value = Strings::replace($string->value, self::STATIC_DATE_REGEX);
        // Handle add/sub multiple times
        while ($match = Strings::match($string->value, self::PLUS_MINUS_COUNT_REGEX)) {
            $methodCall = $this->createModifyMethodCall($carbonCall, new Int_((int) $match['count']), $match['unit'], $match['operator']);
            if ($methodCall instanceof MethodCall) {
                $carbonCall = $methodCall;
                $string->value = Strings::replace($string->value, self::PLUS_MINUS_COUNT_REGEX, '', 1);
            }
        }
        // If we still have something in the string, we go back to the first method and replace this with a parse
        if (($rest = Strings::trim($string->value)) !== '') {
            $currentCall = $carbonCall;
            $callStack = [];
            while ($currentCall instanceof MethodCall) {
                $callStack[] = $currentCall;
                $currentCall = $currentCall->var;
            }
            if (!$currentCall instanceof StaticCall) {
                return $carbonCall;
            }
            // If we fallback to a parse we want to include tomorrow/today/yesterday etc
            if ($currentCall->name instanceof Identifier && $currentCall->name->name !== 'now') {
                $rest .= ' ' . $currentCall->name->name;
            }
            $currentCall->name = new Identifier('parse');
            $currentCall->args = [new Arg(new String_($rest))];
            // Rebuild original call from callstack
            $carbonCall = $this->rebuildCallStack($currentCall, $callStack);
        }
        return $carbonCall;
    }
    private function createStaticCall(FullyQualified $carbonFullyQualified, String_ $string) : StaticCall
    {
        $startDate = Strings::match($string->value, self::STATIC_DATE_REGEX)[0] ?? 'now';
        return new StaticCall($carbonFullyQualified, new Identifier($startDate));
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $carbonCall
     */
    private function createModifyMethodCall($carbonCall, Int_ $int, string $unit, string $operator) : ?MethodCall
    {
        switch ($unit) {
            case 'sec':
            case 'second':
            case 'seconds':
                $unit = 'seconds';
                break;
            case 'min':
            case 'minute':
            case 'minutes':
                $unit = 'minutes';
                break;
            case 'hour':
            case 'hours':
                $unit = 'hours';
                break;
            case 'day':
            case 'days':
                $unit = 'days';
                break;
            case 'week':
            case 'weeks':
                $unit = 'weeks';
                break;
            case 'month':
            case 'months':
                $unit = 'months';
                break;
            case 'year':
            case 'years':
                $unit = 'years';
                break;
            default:
                $unit = null;
                break;
        }
        switch ($operator) {
            case '+':
                $operator = 'add';
                break;
            case '-':
                $operator = 'sub';
                break;
            default:
                $operator = null;
                break;
        }
        if ($unit === null || $operator === null) {
            return null;
        }
        $methodName = $operator . \ucfirst($unit);
        return new MethodCall($carbonCall, new Identifier($methodName), [new Arg($int)]);
    }
    /**
     * @param MethodCall[] $callStack
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall
     */
    private function rebuildCallStack(StaticCall $staticCall, array $callStack)
    {
        if ($callStack === []) {
            return $staticCall;
        }
        $currentCall = $staticCall;
        $callStack = \array_reverse($callStack);
        foreach ($callStack as $call) {
            $call->var = $currentCall;
            $currentCall = $call;
        }
        return $currentCall;
    }
}
