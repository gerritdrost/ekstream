<?php

namespace GerritDrost\Ekstream\Stream;

use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    public function testLinesFromResource()
    {
        $lines = [
            'foo',
            'bar',
            'foobar',
        ];

        // Create a resource
        $resource = tmpfile();

        // Write the lines
        fwrite($resource, implode(PHP_EOL, $lines));
        fflush($resource);

        // Rewind the stream
        rewind($resource);

        // Now we read
        $readLines = File::linesFromResource($resource, true)->toArray();

        // Close the resource
        fclose($resource);

        // Should be equal
        $this->assertEquals($lines, $readLines);
    }

    /**
     *
     */
    public function testLinesFromFile()
    {
        $lines = [
            'foo',
            'bar',
            'foobar',
        ];

        // Create a resource
        $tmpFile = tempnam(sys_get_temp_dir(), 'ekstream-');

        register_shutdown_function(
            function () use ($tmpFile) {
                @unlink($tmpFile);
            }
        );

        $resource = fopen($tmpFile, 'w+');

        // Write the lines
        fwrite($resource, implode(PHP_EOL, $lines));
        fflush($resource);

        // Rewind the stream
        rewind($resource);

        // Now we read
        $readLines = File::linesFromResource($resource, true)->toArray();

        // Close the resource
        fclose($resource);

        // Delete the file
        @unlink($tmpFile);

        // Should be equal
        $this->assertEquals($lines, $readLines);
    }
}