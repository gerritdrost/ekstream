<?php

namespace GerritDrost;

use Closure;
use Generator;
use InvalidArgumentException;

class River
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
     * @return River
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
     * @return River
     */
    public static function fromGenerator(Generator $generator)
    {
        return new self($generator);
    }

    /**
     * @return River
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
     * @return River
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

        return River::fromGenerator($newGenerator());
    }

    /**
     * @param callable $mapFunction
     *
     * @return River
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

        return River::fromGenerator($newGenerator());
    }

    /**
     * @param mixed    $initialValue
     * @param callable $reduceFunction
     *
     * @return mixed
     */
    public function reduce($initialValue, callable $reduceFunction)
    {
        $result = $initialValue;

        $generator = $this->yieldGenerator();

        foreach ($generator as $value) {
            $result = $reduceFunction($result, $value);
        }

        return $result;
    }

    /**
     * @param int $n
     *
     * @return River
     */
    public function limit(int $n)
    {
        if ($n < 0) {
            throw new InvalidArgumentException('River::limit requires a positive integer as parameter');
        }

        if ($n === 0) {
            return River::fromArray([]);
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

        return River::fromGenerator($newGenerator());
    }

    /**
     * @param callable $flatMapFunction
     *
     * @return River
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

                if ($innerStream instanceof River)
                    $innerGenerator = $innerStream->generator;
                else
                    throw new Exception\WrongOutputType(River::class, self::getType($innerStream));

                foreach ($innerGenerator as $innerValue) {
                    yield $innerValue;
                }
            }
        };

        return River::fromGenerator($newGenerator());
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