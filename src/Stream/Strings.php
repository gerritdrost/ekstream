<?php

namespace GerritDrost\Ekstream\Stream;

use Generator;
use GerritDrost\Ekstream\Stream;

class Strings
{
    /**
     * @param string $string
     *
     * @return Generator
     */
    private static function createCharGenerator(string &$string)
    {
        $generator = function () use (&$string) {
            $length = mb_strlen($string);

            for ($i = 0; $i < $length; $i++) {
                yield mb_substr($string, $i, 1);
            }
        };

        return $generator();
    }

    /**
     * @param string $string
     *
     * @return Stream
     */
    public static function chars(string $string)
    {
        return Stream::fromGenerator(self::createCharGenerator($string));
    }

    /**
     * @param string $string
     * @param array $chars
     *
     * @return Stream
     */
    public static function split(string $string, array $chars = ["\t", "\r", "\n", ' ']) {
        $generator = function() use (&$string, &$chars) {
            $charGenerator = self::createCharGenerator($string);

            $word = '';
            $wordLength = 0;

            foreach ($charGenerator as $char) {
                if (in_array($char, $chars, true) && $wordLength > 0) {
                    yield $word;
                    $word = '';
                    $wordLength = 0;
                } else {
                    $word .= $char;
                    $wordLength++;
                }
            }

            if ($wordLength > 0) {
                yield $word;
            }
        };

        return Stream::fromGenerator($generator());
    }
}