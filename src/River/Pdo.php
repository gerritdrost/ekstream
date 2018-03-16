<?php

namespace GerritDrost\River;

use GerritDrost\River;
use PDOStatement;

class Pdo
{
    /**
     * @param PDOStatement $statement
     *
     * @return River
     */
    public static function fetchAssoc(PDOStatement $statement)
    {
        return self::fetch($statement, \PDO::FETCH_ASSOC);
    }

    /**
     * @param PDOStatement $statement
     *
     * @return River
     */
    public static function fetchColumn(PDOStatement $statement)
    {
        return self::fetch($statement, \PDO::FETCH_COLUMN);
    }

    /**
     * @param PDOStatement $statement
     *
     * @return River
     */
    public static function fetchNum(PDOStatement $statement)
    {
        return self::fetch($statement, \PDO::FETCH_NUM);
    }

    /**
     * @param PDOStatement $statement
     *
     * @return River
     */
    public static function fetchBoth(PDOStatement $statement)
    {
        return self::fetch($statement, \PDO::FETCH_BOTH);
    }

    /**
     * @param PDOStatement $statement
     * @param int|null     $fetchStyle
     *
     * @return River
     */
    private static function fetch(PDOStatement $statement, int $fetchStyle)
    {
        $generator = function () use ($statement, $fetchStyle) {
            while (($record = $statement->fetch($fetchStyle)) !== false) {
                yield $record;
            }
        };

        return River::fromGenerator($generator());
    }
}