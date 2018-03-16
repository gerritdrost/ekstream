<?php

namespace GerritDrost\River;

use GerritDrost\River;
use InvalidArgumentException;

class Bytes
{
    /**
     * @param resource $resource
     * @param int      $bufferSize
     *
     * @return River
     */
    public static function fromResource($resource, int $bufferSize = 1)
    {
        if ($bufferSize <= 0) {
            throw new InvalidArgumentException("Buffer size must be a positive integer, {$bufferSize} given");
        } else {
            $generator = function () use ($resource, $bufferSize) {
                while (($buffer = fread($resource, $bufferSize)) !== false) {
                    $len = strlen($buffer);

                    for ($i = 0; $i < $len; $i++) {
                        yield $buffer[$i];
                    }
                }
            };
        }

        return new self($generator());
    }

    /**
     * @param string $byteString
     *
     * @return River
     */
    public static function fromByteString(string $byteString)
    {
        $generator = function () use (&$byteString) {
            $length = strlen($byteString);

            for ($i = 0; $i < $length; $i++) {
                yield $byteString[$i];
            }
        };

        return River::fromGenerator($generator());
    }
}