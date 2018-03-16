<?php

namespace GerritDrost\River;

use GerritDrost\River;

class Numbers
{
    /**
     * Sauce: http://stackoverflow.com/a/20460461
     *
     * @param int $n
     *
     * @return int
     */
    private static function sign(int $n): int
    {
        return (int)($n > 0) - (int)($n < 0);
    }

    /**
     * @param int $start
     * @param int $step
     *
     * @return River
     */
    public static function generate(int $start, int $step = 1): River
    {
        $generator = function() use (&$start, &$step) {
            for ($i = $start; true; $i += $step) {
                yield $i;
            }
        };

        return River::fromGenerator($generator());
    }

    /**
     * @param int $start
     * @param int $end
     * @param int $step
     *
     * @return River
     */
    public static function range(int $start, int $end, int $step = 1): River
    {
        $diff = $end - $start;

        $diffSign = self::sign($diff);
        $stepSign = self::sign($step);

        if ($diffSign !== $stepSign) {
            return River::fromArray([$start ]);
        }

        if ($diffSign > 0) {
            $generator = function() use (&$start, &$end, &$step) {
                for ($i = $start; $i <= $end; $i += $step) {
                    yield $i;
                }
            };
            return River::fromGenerator($generator());
        } else {
            $generator = function() use (&$start, &$end, &$step) {
                for ($i = $start; $i >= $end; $i += $step) {
                    yield $i;
                }
            };
            return River::fromGenerator($generator());
        }
    }
}