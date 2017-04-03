<?php

namespace GerritDrost\Ekstream\Stream;

use GerritDrost\Ekstream\Stream;

class Pdo
{
    /**
     * @return Stream
     */
    public static function fromStatement(\PDOStatement $statement, int $fetchStyle = null)
    {
        $generator = function() use ($statement, $fetchStyle) {
              $record = $statement->fetch($fetchStyle);

              if ($record === false)
                  return;
              else
                  yield $record;
        };

        return Stream::fromGenerator($generator());
    }
}