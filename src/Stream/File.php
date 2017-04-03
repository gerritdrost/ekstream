<?php

namespace GerritDrost\Ekstream\Stream;

use GerritDrost\Ekstream\Stream;
use InvalidArgumentException;

class File
{
    /**
     * @return Stream
     */
    public static function linesFromResource(resource $handle)
    {
        if (!is_resource($handle)) {
            throw new InvalidArgumentException('Provided resource is not valid');
        }

        $generator = function () use ($handle) {
            $line = fgets($handle);

            if ($line === false)
                return;
            else
                yield $line;
        };

        return Stream::fromGenerator($generator());
    }

    /**
     * @param string $path
     *
     * @return Stream
     */
    public static function linesFromFile(string $path)
    {
        if (!file_exists($path))
            throw new InvalidArgumentException('Provided path does not exist or is not a file');

        $handle = fopen($path, 'r');

        if (!is_resource($handle))
            throw new InvalidArgumentException('Provided resource is not valid');

        $generator = function () use ($handle) {
            try {
                while (($line = fgets($handle)) !== false) {
                    yield $line;
                }
            } finally {
                @fclose($handle);
            }
        };

        return Stream::fromGenerator($generator());
    }
}