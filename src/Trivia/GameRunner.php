<?php

declare(strict_types=1);

namespace ConveyCode\Trivia;

require __DIR__ . '/../../vendor/autoload.php';

$aGame = new Game();

$aGame->add("Chet");
$aGame->add("Pat");
$aGame->add("Sue");

$minAnswerId = 0;
$maxAnswerId = 9;
$wrongAnswerId = 7;

do {
    $dice = rand(0, 5) + 1;
    $aGame->roll($dice);

    if (rand($minAnswerId, $maxAnswerId) == $wrongAnswerId) {
        $notAWinner = $aGame->wrongAnswer();
    } else {
        $notAWinner = $aGame->wasCorrectlyAnswered();
    }
} while ($notAWinner);
