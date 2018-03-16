<?php

namespace GerritDrost\River;

use PHPUnit\Framework\TestCase;

class NumbersTest extends TestCase
{
    /**
     * @dataProvider rangeProvider
     *
     * @param int   $start
     * @param int   $end
     * @param int   $step
     * @param array $expectedValues
     */
    public function testRange(int $start, int $end, int $step, array $expectedValues)
    {
        $this->assertEquals(
            $expectedValues,
            Numbers
                ::range($start, $end, $step)
                ->toArray()
        );
    }

    /**
     * @return array
     */
    public function rangeProvider(): array
    {
        return [
            [0, 5, 1, [0, 1, 2, 3, 4, 5]],
            [5, 0, -1, [5, 4, 3, 2, 1, 0]],
            [0, 8, 2, [0, 2, 4, 6, 8]],
            [8, 0, -2, [8, 6, 4, 2, 0]],
            [0, 9, 2, [0, 2, 4, 6, 8]],
            [9, 0, -2, [9, 7, 5, 3, 1]],
            [5, 7, 10, [5]],
            [5, -1, 10, [5]]
        ];
    }

    /**
     * @dataProvider generateProvider
     *
     * @param int   $start
     * @param int   $step
     * @param int   $limit
     * @param array $expectedValues
     */
    public function testGenerate(int $start, int $step, int $limit, array $expectedValues)
    {
        $this->assertEquals(
            $expectedValues,
            Numbers
                ::generate($start, $step)
                ->limit($limit)
                ->toArray()
        );
    }

    /**
     * @return array
     */
    public function generateProvider(): array
    {
        return [
            [0, 5, 2, [0, 5]],
            [0, -3, 4, [0, -3, -6, -9]],
            [0, 0, 3, [0, 0, 0]],
            [0, 0, 0, []]
        ];
    }
}