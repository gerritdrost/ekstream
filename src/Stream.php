<?php

namespace GerritDrost\Ekstream;

use Closure;
use Generator;
use InvalidArgumentException;

class Stream
{
    /**
     * @var Generator the backing generator
     */
    private $generator;

    /**
     * @param Generator $generator
     */
    private function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param array $array
     *
     * @return Stream
     */
    public static function fromArray(array $array)
    {
        $generator = function () use (&$array) {
            yield from $array;
        };

        return new self($generator());
    }

    /**
     * @param Generator $generator
     *
     * @return Stream
     */
    public static function fromGenerator(Generator $generator)
    {
        return new self($generator);
    }

    /**
     * @return Stream
     */
    public static function emptySet()
    {
        return new self(yield from []);
    }

    /**
     * @param callable $callable
     *
     * @return Stream
     */
    public function filter(callable $callable)
    {
        $closure = Closure::fromCallable($callable);

        $generator = $this->yieldGenerator();

        $newGenerator = function () use (&$generator, &$closure) {
            foreach ($generator as $value) {
                $mayYield = $closure($value);

                if ($mayYield === true)
                    yield $value;
            }
        };

        return Stream::fromGenerator($newGenerator());
    }

    /**
     * @param callable $callable
     *
     * @return Stream
     */
    public function map(callable $callable)
    {
        $closure = Closure::fromCallable($callable);

        $generator = $this->yieldGenerator();

        $newGenerator = function () use (&$generator, &$closure) {
            foreach ($generator as $value) {
                yield $closure($value);
            }
        };

        return Stream::fromGenerator($newGenerator());
    }

    /**
     * @param int $n
     *
     * @return Stream
     */
    public function limit(int $n)
    {
        if ($n < 0)
            throw new InvalidArgumentException('Stream::limit requires a positive integer as parameter');

        $generator = $this->yieldGenerator();

        $newGenerator = function () use (&$generator, &$n) {
            $c = 0;

            foreach ($generator as $value) {
                yield $value;

                $c++;

                if ($c === $n)
                    return;
            }
        };

        return Stream::fromGenerator($newGenerator());
    }

    /**
     * @param callable $callable
     *
     * @return Stream
     *
     * @throws Exception\WrongOutputType
     */
    public function flatMap(callable $callable)
    {
        $closure = Closure::fromCallable($callable);

        $generator = $this->yieldGenerator();

        $newGenerator = function () use (&$generator, &$closure) {
            foreach ($generator as $value) {
                $innerStream = $closure($value);

                if ($innerStream instanceof Stream)
                    $innerGenerator = $innerStream->generator;
                else
                    throw new Exception\WrongOutputType(Stream::class, self::getType($innerStream));

                foreach ($innerGenerator as $innerValue) {
                    yield $innerValue;
                }
            }
        };

        return Stream::fromGenerator($newGenerator());
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return iterator_to_array($this->yieldGenerator(), false);
    }

    /**
     * @return mixed
     *
     * @throws Exception\ElementNotFound
     */
    public function getFirst()
    {
        foreach ($this->yieldGenerator() as $value) {
            return $value;
        }

        throw new Exception\ElementNotFound();
    }

    /**
     * @return Generator
     */
    public function toGenerator()
    {
        return $this->yieldGenerator();
    }

    /**
     * @param callable $callable
     */
    public function walk(callable $callable)
    {
        $closure = Closure::fromCallable($callable);

        foreach ($this->yieldGenerator() as $value) {
            $closure($value);
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        $generator = $this->yieldGenerator();

        $c = 0;

        foreach ($generator as $value)
            $c++;

        return $c;
    }

    /**
     * @return Generator
     */
    private function &yieldGenerator()
    {
        $generator = $this->generator;

        $emptyGenerator = function() {
            yield from [];
        };

        $this->generator = $emptyGenerator();

        return $generator;
    }

    /**
     * @param mixed $var
     *
     * @return string
     */
    private static function getType($var)
    {
        if (is_object($var))
            return get_class($var);
        if (is_null($var))
            return 'null';
        if (is_scalar($var))
            return gettype($var);
        if (is_array($var))
            return 'array';
        if (is_resource($var))
            return 'resource';
        else
            return 'unknown';
    }
}