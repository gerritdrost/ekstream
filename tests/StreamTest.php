<?php

namespace GerritDrost\Ekstream;

use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
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
            Stream
                ::fromArray($numerals)
                ->count()
        );

        $this->assertEquals(
            $numerals,
            Stream
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
            Stream
                ::fromGenerator($generator())
                ->toArray()
        );

        $this->assertEquals(
            3,
            Stream
                ::fromGenerator($generator())
                ->count()
        );
    }
}