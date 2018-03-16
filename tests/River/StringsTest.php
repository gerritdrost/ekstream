<?php

namespace GerritDrost\River;

use PHPUnit\Framework\TestCase;

class StringsTest extends TestCase
{
    /**
     * @dataProvider charsProvider
     *
     * @param string... $chars
     */
    public function testChars(string $string, string... $chars)
    {
        $this->assertEquals(
            $chars,
            Strings::chars($string)
                ->toArray()
        );
    }

    /**
     * @return array
     */
    public function charsProvider(): array
    {
        return [
            ['foobar', 'f', 'o', 'o', 'b', 'a', 'r'],
            [''],
            ['€äĉ💩', '€', 'ä', 'ĉ', '💩'],
        ];
    }

    /**
     * @dataProvider splitProvider
     *
     * @param string... $chars
     * @param string[] $splitChars
     * @param string[] $segments
     */
    public function testSplit(string $string, array $splitChars, string... $segments)
    {
        $this->assertEquals(
            $segments,
            Strings::split($string, $splitChars)
                ->toArray()
        );
    }

    /**
     * @return array
     */
    public function splitProvider(): array
    {
        return [
            ['foobar', ['b'], 'foo', 'ar'],
            ["line1\nline2", ["\n"], 'line1', 'line2'],
            ["a\tb\tc\td", ["\t"], 'a', 'b', 'c', 'd']
        ];
    }
}