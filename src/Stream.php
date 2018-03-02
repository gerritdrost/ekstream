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
        $emptyGenerator = function () {
            yield from [];
        };

        return new self($emptyGenerator());
    }

    /**
     * @param callable $filterFunction
     *
     * @return Stream
     */
    public function filter(callable $filterFunction)
    {
        $filterFunction = Closure::fromCallable($filterFunction);

        $generator = $this->yieldGenerator();

        $newGenerator = function () use (&$generator, &$filterFunction) {
            foreach ($generator as $value) {
                $mayYield = $filterFunction($value);

                if ($mayYield === true)
                    yield $value;
            }
        };

        return Stream::fromGenerator($newGenerator());
    }

    /**
     * @param callable $mapFunction
     *
     * @return Stream
     */
    public function map(callable $mapFunction)
    {
        $mapFunction = Closure::fromCallable($mapFunction);

        $generator = $this->yieldGenerator();

        $newGenerator = function () use (&$generator, &$mapFunction) {
            foreach ($generator as $value) {
                yield $mapFunction($value);
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
        if ($n < 0) {
            throw new InvalidArgumentException('Stream::limit requires a positive integer as parameter');
        }

        if ($n === 0) {
            return Stream::fromArray([]);
        }

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
     * @param callable $flatMapFunction
     *
     * @return Stream
     *
     * @throws Exception\WrongOutputType
     */
    public function flatMap(callable $flatMapFunction)
    {
        $flatMapFunction = Closure::fromCallable($flatMapFunction);

        $generator = $this->yieldGenerator();

        $newGenerator = function () use (&$generator, &$flatMapFunction) {
            foreach ($generator as $value) {
                $innerStream = $flatMapFunction($value);

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
     * @param callable $identityFunction
     *
     * @return array
     */
    public function toGroupedArray(callable $identityFunction)
    {
        $identityFunction = Closure::fromCallable($identityFunction);

        $array = [];

        $generator = $this->yieldGenerator();

        foreach ($generator as $value) {
            $identity = $identityFunction($value);

            if (isset($array[$identity])) {
                $identityArray = &$array[$identity];
            } else {
                $identityArray    = [];
                $array[$identity] = &$identityArray;
            }

            $identityArray[] = $value;
        }

        return $array;
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
     * @param callable $callable
     *
     * @return mixed
     */
    public function getFirstOrElse(callable $callable)
    {
        $closure = Closure::fromCallable($callable);

        foreach ($this->yieldGenerator() as $value) {
            return $value;
        }

        return $closure();
    }

    /**
     * @param null $orElse
     *
     * @return mixed
     */
    public function getFirstOrElseGet($orElse = null)
    {
        foreach ($this->yieldGenerator() as $value) {
            return $value;
        }

        return $orElse;
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

        $emptyGenerator = function () {
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