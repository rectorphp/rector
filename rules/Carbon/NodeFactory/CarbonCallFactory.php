<?php

declare (strict_types=1);
namespace Rector\Carbon\NodeFactory;

use RectorPrefix202407\Nette\Utils\Strings;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
final class CarbonCallFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/9vGt8r/1
     */
    private const PLUS_DAY_COUNT_REGEX = '#\\+(\\s+)?(?<count>\\d+)(\\s+)?(day|days)#';
    /**
     * @var string
     * @see https://regex101.com/r/pqOPg6/1
     */
    private const MINUS_DAY_COUNT_REGEX = '#-(\\s+)?(?<count>\\d+)(\\s+)?(day|days)#';
    /**
     * @var string
     * @see https://regex101.com/r/6VUUQF/1
     */
    private const PLUS_MONTH_COUNT_REGEX = '#\\+(\\s+)?(?<count>\\d+)(\\s+)?(month|months)#';
    /**
     * @var string
     * @see https://regex101.com/r/IvyT7w/1
     */
    private const MINUS_MONTH_COUNT_REGEX = '#-(\\s+)?(?<count>\\d+)(\\s+)?(month|months)#';
    /**
     * @var array<self::*_REGEX, string>
     */
    private const REGEX_TO_METHOD_NAME_MAP = [self::PLUS_DAY_COUNT_REGEX => 'addDays', self::MINUS_DAY_COUNT_REGEX => 'subDays', self::PLUS_MONTH_COUNT_REGEX => 'addMonths', self::MINUS_MONTH_COUNT_REGEX => 'subMonths'];
    /**
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall
     */
    public function createFromDateTimeString(FullyQualified $carbonFullyQualified, String_ $string)
    {
        $dateTimeValue = $string->value;
        if ($dateTimeValue === 'now') {
            return new StaticCall($carbonFullyQualified, new Identifier('now'));
        }
        if ($dateTimeValue === 'today') {
            return new StaticCall($carbonFullyQualified, new Identifier('today'));
        }
        $hasToday = Strings::match($dateTimeValue, '#today#');
        if ($hasToday !== null) {
            $carbonCall = new StaticCall($carbonFullyQualified, new Identifier('today'));
        } else {
            $carbonCall = new StaticCall($carbonFullyQualified, new Identifier('now'));
        }
        foreach (self::REGEX_TO_METHOD_NAME_MAP as $regex => $methodName) {
            $match = Strings::match($dateTimeValue, $regex);
            if ($match === null) {
                continue;
            }
            $countLNumber = new LNumber((int) $match['count']);
            $carbonCall = new MethodCall($carbonCall, new Identifier($methodName), [new Arg($countLNumber)]);
        }
        return $carbonCall;
    }
}
