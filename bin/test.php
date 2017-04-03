<?php

use GerritDrost\Ekstream\Stream;

require __DIR__ . '/../vendor/autoload.php';

$array = [
    [1, 2, 3],
    [4, 5],
    [6],
    [7]
];

Stream::fromArray($array)
    ->flatMap(
        function ($array) {
            return Stream::fromArray($array);
        }
    )
    ->map(
        function ($value) {
            return $value * $value;
        }
    )
    ->walk(
        function ($value) {
            echo "{$value}\n";
        }
    );

$valueStrings = Stream::fromArray([ 1, 2, 3, 4, 5 ])
    ->limit(3)
    ->map(
        function (int $value) {
            return "Value: {$value}";
        }
    )
    ->toArray();

class HostMapping {
    private $host;
    private $ip;

    /**
     * @param $host
     * @param $ip
     */
    public function __construct(string $host, string $ip)
    {
        $this->host = $host;
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }
}

Stream\File::linesFromFile('/etc/hosts')

    // Trim all whitespace from the lines
    ->map(function(string $line) { return trim($line); })

    // Filter out all empty lines
    ->filter(function(string $line) { return mb_strlen($line) > 0; })

    // Filter out all comments (lines starting with '#')
    ->filter(function(string $line) { return mb_substr($line, 0, 1) !== '#'; })

    // Map to arrays of elements
    ->map(function ($line) { return Stream\Strings::split($line)->toArray(); })

    // Filter out all arrays with less than 2 elements, since they can't be mappings
    ->filter(function (array $array) { return count($array) > 1; })

    // Since one IP can map multiple hosts, we flatMap Streams of HostMapping-instances into one Stream
    ->flatMap(
        function (array $array) {
            $generator = function() use (&$array) {
                $count = count($array);

                $ip = $array[0];

                for ($i = 1; $i < $count; $i++) {
                    yield new HostMapping($ip, $array[$i]);
                }
            };

            return Stream::fromGenerator($generator());
        }
    )

    // Iterate and output to stdout
    ->walk(
        function (HostMapping $hostMapping) {
            echo "{$hostMapping->getHost()} -> {$hostMapping->getIp()}\n";
        }
    );

Stream\Numbers::generate(0, 2)
    ->filter(function ($n) { return $n % 4 === 0; })
    ->limit(10)
    ->walk(function ($n) { echo "{$n}\n"; });
