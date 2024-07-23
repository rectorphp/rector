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
     * @see https://regex101.com/r/dWRjk5/1
     */
    private const PLUS_HOUR_COUNT_REGEX = '#\\+(\\s+)?(?<count>\\d+)(\\s+)?(hour|hours)#';
    /**
     * @var string
     * @see https://regex101.com/r/dfK0Ri/1
     */
    private const PLUS_MINUTE_COUNT_REGEX = '#\\+(\\s+)?(?<count>\\d+)(\\s+)?(minute|minuts)#';
    /**
     * @var string
     * @see https://regex101.com/r/o7QDYL/1
     */
    private const PLUS_SECOND_COUNT_REGEX = '#\\+(\\s+)?(?<count>\\d+)(\\s+)?(second|seconds)#';
    /**
     * @var string
     * @see https://regex101.com/r/IvyT7w/1
     */
    private const MINUS_MONTH_COUNT_REGEX = '#-(\\s+)?(?<count>\\d+)(\\s+)?(month|months)#';
    /**
     * @var string
     * @see https://regex101.com/r/bICKg6/1
     */
    private const MINUS_HOUR_COUNT_REGEX = '#-(\\s+)?(?<count>\\d+)(\\s+)?(hour|hours)#';
    /**
     * @var string
     * @see https://regex101.com/r/WILFvX/1
     */
    private const MINUS_MINUTE_COUNT_REGEX = '#-(\\s+)?(?<count>\\d+)(\\s+)?(minute|minutes)#';
    /**
     * @var string
     * @see https://regex101.com/r/FwCUup/1
     */
    private const MINUS_SECOND_COUNT_REGEX = '#-(\\s+)?(?<count>\\d+)(\\s+)?(second|seconds)#';
    /**
     * @var array<self::*_REGEX, string>
     */
    private const REGEX_TO_METHOD_NAME_MAP = [self::PLUS_DAY_COUNT_REGEX => 'addDays', self::MINUS_DAY_COUNT_REGEX => 'subDays', self::PLUS_MONTH_COUNT_REGEX => 'addMonths', self::MINUS_MONTH_COUNT_REGEX => 'subMonths', self::PLUS_HOUR_COUNT_REGEX => 'addHours', self::MINUS_HOUR_COUNT_REGEX => 'subHours', self::PLUS_MINUTE_COUNT_REGEX => 'addMinutes', self::MINUS_MINUTE_COUNT_REGEX => 'subMinutes', self::PLUS_SECOND_COUNT_REGEX => 'addSeconds', self::MINUS_SECOND_COUNT_REGEX => 'subSeconds'];
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
