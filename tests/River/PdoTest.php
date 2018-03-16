<?php

namespace GerritDrost\River;

use LogicException;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class PdoTest extends TestCase
{
    /**
     * @dataProvider fetchAssocProvider
     *
     * @param mixed[] $expectedValues
     */
    public function testFetchAssoc(...$expectedValues)
    {
        $mockPdoStatement = $this->mockPdoStatement(...$expectedValues);

        $this->assertEquals(
            $expectedValues,
            Pdo
                ::fetchAssoc($mockPdoStatement)
                ->toArray()
        );
    }

    /**
     * @return array
     */
    public function fetchAssocProvider()
    {
        return [
            ['foo' => 1, 'bar' => 2, 'baz' => 3],
            ['foo' => 4, 'bar' => 5, 'baz' => 6],
        ];
    }

    /**
     * @dataProvider fetchNumProvider
     *
     * @param mixed[] $expectedValues
     */
    public function testFetchNum(...$expectedValues)
    {
        $mockPdoStatement = $this->mockPdoStatement(...$expectedValues);

        $this->assertEquals(
            $expectedValues,
            Pdo
                ::fetchNum($mockPdoStatement)
                ->toArray()
        );
    }

    /**
     * @return array
     */
    public function fetchNumProvider()
    {
        return [
            [1, 2, 3],
            [4, 5, 6],
        ];
    }

    /**
     * @dataProvider fetchBothProvider
     *
     * @param mixed[] $expectedValues
     */
    public function testFetchBoth(...$expectedValues)
    {
        $mockPdoStatement = $this->mockPdoStatement(...$expectedValues);

        $this->assertEquals(
            $expectedValues,
            Pdo
                ::fetchBoth($mockPdoStatement)
                ->toArray()
        );
    }

    /**
     * @return array
     */
    public function fetchBothProvider()
    {
        return [
            [0 => 1, 'foo' => 1, 1 => 2, 'bar' => 2, 2 => 3, 'baz' => 3],
            [0 => 4, 'foo' => 4, 1 => 5, 'bar' => 1, 2 => 6, 'baz' => 6],
        ];
    }

    /**
     * @dataProvider fetchColumnProvider
     *
     * @param mixed[] $expectedValues
     */
    public function testFetchColumn(...$expectedValues)
    {
        $mockPdoStatement = $this->mockPdoStatement(...$expectedValues);

        $this->assertEquals(
            $expectedValues,
            Pdo
                ::fetchColumn($mockPdoStatement)
                ->toArray()
        );
    }

    /**
     * @return array
     */
    public function fetchColumnProvider()
    {
        return [
            [1],
            [2],
            [3],
        ];
    }

    /**
     * @param array ...$values
     *
     * @return PDOStatement
     */
    private function mockPdoStatement(...$values): PDOStatement
    {
        $values[] = false;

        try {
        $mockPdoStatement = $this->createMock(PDOStatement::class);

        $mockPdoStatement
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(...$values);
        } catch (ReflectionException $e) {
            throw new LogicException('This should not happen', 0, $e);
        }

        /* @var PDOStatement $mockPdoStatement */

        return $mockPdoStatement;
    }
}