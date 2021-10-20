<?php

/*
 * This file is part of composer/semver.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Composer\Semver;

use RectorPrefix20211020\Composer\Semver\Constraint\Constraint;
use RectorPrefix20211020\Composer\Semver\Constraint\ConstraintInterface;
/**
 * Helper class to evaluate constraint by compiling and reusing the code to evaluate
 */
class CompilingMatcher
{
    /**
     * @var array
     * @phpstan-var array<string, callable>
     */
    private static $compiledCheckerCache = array();
    /** @var bool */
    private static $enabled;
    /**
     * @phpstan-var array<Constraint::OP_*, Constraint::STR_OP_*>
     */
    private static $transOpInt = array(\RectorPrefix20211020\Composer\Semver\Constraint\Constraint::OP_EQ => \RectorPrefix20211020\Composer\Semver\Constraint\Constraint::STR_OP_EQ, \RectorPrefix20211020\Composer\Semver\Constraint\Constraint::OP_LT => \RectorPrefix20211020\Composer\Semver\Constraint\Constraint::STR_OP_LT, \RectorPrefix20211020\Composer\Semver\Constraint\Constraint::OP_LE => \RectorPrefix20211020\Composer\Semver\Constraint\Constraint::STR_OP_LE, \RectorPrefix20211020\Composer\Semver\Constraint\Constraint::OP_GT => \RectorPrefix20211020\Composer\Semver\Constraint\Constraint::STR_OP_GT, \RectorPrefix20211020\Composer\Semver\Constraint\Constraint::OP_GE => \RectorPrefix20211020\Composer\Semver\Constraint\Constraint::STR_OP_GE, \RectorPrefix20211020\Composer\Semver\Constraint\Constraint::OP_NE => \RectorPrefix20211020\Composer\Semver\Constraint\Constraint::STR_OP_NE);
    /**
     * Evaluates the expression: $constraint match $operator $version
     *
     * @param ConstraintInterface $constraint
     * @param int                 $operator
     * @phpstan-param Constraint::OP_*  $operator
     * @param string              $version
     *
     * @return mixed
     */
    public static function match($constraint, $operator, $version)
    {
        if (self::$enabled === null) {
            self::$enabled = !\in_array('eval', \explode(',', (string) \ini_get('disable_functions')), \true);
        }
        if (!self::$enabled) {
            return $constraint->matches(new \RectorPrefix20211020\Composer\Semver\Constraint\Constraint(self::$transOpInt[$operator], $version));
        }
        $cacheKey = $operator . $constraint;
        if (!isset(self::$compiledCheckerCache[$cacheKey])) {
            $code = $constraint->compile($operator);
            self::$compiledCheckerCache[$cacheKey] = $function = eval('return function($v, $b){return ' . $code . ';};');
        } else {
            $function = self::$compiledCheckerCache[$cacheKey];
        }
        return $function($version, \strpos($version, 'dev-') === 0);
    }
}
