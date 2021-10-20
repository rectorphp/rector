<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\VarDumper\Caster;

use RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub;
/**
 * Casts DateTimeInterface related classes to array representation.
 *
 * @author Dany Maillard <danymaillard93b@gmail.com>
 *
 * @final
 */
class DateCaster
{
    private const PERIOD_LIMIT = 3;
    public static function castDateTime(\DateTimeInterface $d, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested, int $filter)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        $location = $d->getTimezone()->getLocation();
        $fromNow = (new \DateTime())->diff($d);
        $title = $d->format('l, F j, Y') . "\n" . self::formatInterval($fromNow) . ' from now' . ($location ? $d->format('I') ? "\nDST On" : "\nDST Off" : '');
        unset($a[\RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_DYNAMIC . 'date'], $a[\RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_DYNAMIC . 'timezone'], $a[\RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_DYNAMIC . 'timezone_type']);
        $a[$prefix . 'date'] = new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ConstStub(self::formatDateTime($d, $location ? ' e (P)' : ' P'), $title);
        $stub->class .= $d->format(' @U');
        return $a;
    }
    public static function castInterval(\DateInterval $interval, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested, int $filter)
    {
        $now = new \DateTimeImmutable();
        $numberOfSeconds = $now->add($interval)->getTimestamp() - $now->getTimestamp();
        $title = \number_format($numberOfSeconds, 0, '.', ' ') . 's';
        $i = [\RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL . 'interval' => new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ConstStub(self::formatInterval($interval), $title)];
        return $filter & \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::EXCLUDE_VERBOSE ? $i : $i + $a;
    }
    private static function formatInterval(\DateInterval $i) : string
    {
        $format = '%R ';
        if (0 === $i->y && 0 === $i->m && ($i->h >= 24 || $i->i >= 60 || $i->s >= 60)) {
            $i = \date_diff($d = new \DateTime(), \date_add(clone $d, $i));
            // recalculate carry over points
            $format .= 0 < $i->days ? '%ad ' : '';
        } else {
            $format .= ($i->y ? '%yy ' : '') . ($i->m ? '%mm ' : '') . ($i->d ? '%dd ' : '');
        }
        $format .= $i->h || $i->i || $i->s || $i->f ? '%H:%I:' . self::formatSeconds($i->s, \substr($i->f, 2)) : '';
        $format = '%R ' === $format ? '0s' : $format;
        return $i->format(\rtrim($format));
    }
    public static function castTimeZone(\DateTimeZone $timeZone, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested, int $filter)
    {
        $location = $timeZone->getLocation();
        $formatted = (new \DateTime('now', $timeZone))->format($location ? 'e (P)' : 'P');
        $title = $location && \extension_loaded('intl') ? \Locale::getDisplayRegion('-' . $location['country_code']) : '';
        $z = [\RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL . 'timezone' => new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ConstStub($formatted, $title)];
        return $filter & \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::EXCLUDE_VERBOSE ? $z : $z + $a;
    }
    public static function castPeriod(\DatePeriod $p, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested, int $filter)
    {
        $dates = [];
        foreach (clone $p as $i => $d) {
            if (self::PERIOD_LIMIT === $i) {
                $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
                $dates[] = \sprintf('%s more', ($end = $p->getEndDate()) ? \ceil(($end->format('U.u') - $d->format('U.u')) / ((int) $now->add($p->getDateInterval())->format('U.u') - (int) $now->format('U.u'))) : $p->recurrences - $i);
                break;
            }
            $dates[] = \sprintf('%s) %s', $i + 1, self::formatDateTime($d));
        }
        $period = \sprintf('every %s, from %s (%s) %s', self::formatInterval($p->getDateInterval()), self::formatDateTime($p->getStartDate()), $p->include_start_date ? 'included' : 'excluded', ($end = $p->getEndDate()) ? 'to ' . self::formatDateTime($end) : 'recurring ' . $p->recurrences . ' time/s');
        $p = [\RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL . 'period' => new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ConstStub($period, \implode("\n", $dates))];
        return $filter & \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::EXCLUDE_VERBOSE ? $p : $p + $a;
    }
    private static function formatDateTime(\DateTimeInterface $d, string $extra = '') : string
    {
        return $d->format('Y-m-d H:i:' . self::formatSeconds($d->format('s'), $d->format('u')) . $extra);
    }
    private static function formatSeconds(string $s, string $us) : string
    {
        return \sprintf('%02d.%s', $s, 0 === ($len = \strlen($t = \rtrim($us, '0'))) ? '0' : ($len <= 3 ? \str_pad($t, 3, '0') : $us));
    }
}
