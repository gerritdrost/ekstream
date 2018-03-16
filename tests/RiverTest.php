<?php

namespace GerritDrost;

use PHPUnit\Framework\TestCase;

class RiverTest extends TestCase
{
    public function testFromArray()
    {
        $numerals = [
            '',
            1,
            'two',
            'iii',
            'Δʹ',
        ];

        $this->assertEquals(
            count($numerals),
            River
                ::fromArray($numerals)
                ->count()
        );

        $this->assertEquals(
            $numerals,
            River
                ::fromArray($numerals)
                ->toArray()
        );
    }

    public function testFromGenerator()
    {
        $generator = function() {
            yield 'Orange';
            yield 'Red';
            yield 'Brown';
        };

        $this->assertEquals(
            [
                'Orange',
                'Red',
                'Brown',
            ],
            River
                ::fromGenerator($generator())
                ->toArray()
        );

        $this->assertEquals(
            3,
            River
                ::fromGenerator($generator())
                ->count()
        );
    }
}