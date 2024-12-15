<?php

declare(strict_types=1);

namespace ConveyCode\Tests;

use PHPUnit\Framework\TestCase;

final class GoldenMasterTest extends TestCase
{
    public function testGenerateOutput(): void
    {
        $times = 20000;
        $this->generateManyOutputs($times, '/tmp/gm.txt');
        $this->generateManyOutputs($times, '/tmp/gm2.txt');
        $fileContentGm = file_get_contents('/tmp/gm.txt');
        $fileContentGm2 = file_get_contents('/tmp/gm2.txt');

        $this->assertSame($fileContentGm, $fileContentGm2);
    }

    private function generateManyOutputs(int $times, string $filename): void
    {
        $first = true;
        for ($time = 0; $time < $times; $time++) {
            if ($first) {
                file_put_contents($filename, $this->generateOutput($time));
                $first = false;
            } else {
                file_put_contents($filename, $this->generateOutput($time), FILE_APPEND);
            }
        }
    }

    private function generateOutput(int $seed): string
    {
        ob_start();
        mt_srand($seed);
        require __DIR__ . '/../src/Trivia/GameRunner.php';
        return ob_get_clean();
    }
}
