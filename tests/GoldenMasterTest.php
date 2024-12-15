<?php

declare(strict_types=1);

namespace ConveyCode\Tests;

use PHPUnit\Framework\TestCase;

final class GoldenMasterTest extends TestCase
{
    private const GM_PATH = __DIR__ .  '/gm.txt';

    public function testGenerateOutput(): void
    {
        $this->markTestSkipped('This test is only for generating the golden master');
        $times = 20000;
        $this->generateManyOutputs($times, self::GM_PATH);
    }

    public function testOutputMatchesGoldenMaster(): void
    {
        $times = 20000;
        $actualPath = '/tmp/actual.txt';
        $this->generateManyOutputs($times, $actualPath);
        $expectedContent = file_get_contents(self::GM_PATH);
        $actualContent = file_get_contents($actualPath);

        $this->assertSame($expectedContent, $actualContent);
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
