<?php

namespace GerritDrost\Ekstream\Stream;

use GerritDrost\Ekstream\Stream;
use InvalidArgumentException;

class File
{
    /**
     * @param resource $handle
     *
     * @param bool     $stripTrailingEol
     *
     * @return Stream
     */
    public static function linesFromResource($handle, bool $stripTrailingEol = true): Stream
    {
        if (!is_resource($handle)) {
            throw new InvalidArgumentException('Provided resource is not valid');
        }

        if ($stripTrailingEol) {
            $generator = function () use ($handle) {
                while (($line = fgets($handle)) !== false) {
                    yield self::stripTrailingEol($line);
                }
            };
        } else {
            $generator = function () use ($handle) {
                while (($line = fgets($handle)) !== false) {
                    yield $line;
                }
            };
        }

        return Stream::fromGenerator($generator());
    }

    /**
     * @param string $path
     *
     * @param bool   $stripTrailingEol
     *
     * @return Stream
     *
     */
    public static function linesFromFile(string $path, bool $stripTrailingEol = true): Stream
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException('Provided path does not exist or is not a file');
        }

        $handle = fopen($path, 'r');

        if (!is_resource($handle)) {
            throw new InvalidArgumentException('Provided resource is not valid');
        }

        if ($stripTrailingEol) {
            $generator = function () use ($handle) {
                while (($line = fgets($handle)) !== false) {
                    yield self::stripTrailingEol($line);
                }
            };
        } else {
            $generator = function () use ($handle) {
                while (($line = fgets($handle)) !== false) {
                    yield $line;
                }
            };
        }
        $generator = function () use ($handle) {
            try {
                while (($line = fgets($handle)) !== false) {
                    $lineLength = mb_strlen($line);

                    if ($lineLength > 0 && mb_substr($line, -1) === '\n') {
                        yield mb_substr($line, 0, $lineLength - 1);
                    } else {
                        yield $line;
                    }
                }
            } finally {
                @fclose($handle);
            }
        };

        return Stream::fromGenerator($generator());
    }

    /**
     * @param string $line
     *
     * @return string
     */
    private static function stripTrailingEol(string $line): string
    {
        $lineLength = mb_strlen($line);

        if ($lineLength > 0 && mb_substr($line, -1) === "\n") {
            return mb_substr($line, 0, $lineLength - 1);
        } else {
            return $line;
        }
    }
}