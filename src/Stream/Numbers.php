<?php

namespace GerritDrost\Ekstream\Stream;

use GerritDrost\Ekstream\Stream;
use InvalidArgumentException;

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
     * @return Stream
     */
    public static function generate(int $start, int $step): Stream
    {
        $generator = function() use (&$start, &$step) {
            for ($i = $start; true; $i += $step) {
                yield $i;
            }
        };

        return Stream::fromGenerator($generator());
    }

    /**
     * @param int $start
     * @param int $end
     * @param int $step
     *
     * @return Stream
     *
     * @throws InvalidArgumentException
     */
    public static function range(int $start, int $end, int $step = 1): Stream
    {
        $diff = $end - $start;

        $diffSign = self::sign($diff);
        $stepSign = self::sign($step);

        if ($diffSign !== $stepSign) {
            return Stream::fromArray([ $start ]);
        }

        // todo: only allow "exact" finishes (start = 0, end = 8, step = 3 should not be allowed)
        if ($diffSign > 0) {
            $generator = function() use (&$start, &$end, &$step) {
                for ($i = $start; $i <= $end; $i += $step) {
                    yield $i;
                }
            };
            return Stream::fromGenerator($generator());
        } else {
            $generator = function() use (&$start, &$end, &$step) {
                for ($i = $start; $i >= $end; $i += $step) {
                    yield $i;
                }
            };
            return Stream::fromGenerator($generator());
        }
    }
}