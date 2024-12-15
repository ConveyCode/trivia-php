## Comprendre la code base

Comme nous n'avons pas de tests, nous devons comprendre le code pour pouvoir le tester.

Nous avons 2 fichiers :  `Game.php` et `GameRunner.php`

Exécutons le code pour voir ce qu'il fait : 

```bash
php src/Trivia/GameRunner.php
Chet was added
They are player number 1
Pat was added
They are player number 2
Sue was added
They are player number 3
Chet is the current player
They have rolled a 4
Chet's new location is 4
The category is Pop
Pop Question 0
Answer was corrent!!!!
Chet now has 1 Gold Coins.
Pat is the current player
They have rolled a 5
Pat's new location is 5
The category is Science
Science Question 0
Answer was corrent!!!!
Pat now has 1 Gold Coins.
Sue is the current player
They have rolled a 2
Sue's new location is 2
The category is Sports
Sports Question 0
Answer was corrent!!!!
Sue now has 1 Gold Coins.
Chet is the current player
They have rolled a 2
Chet's new location is 6
The category is Sports
Sports Question 1
Question was incorrectly answered
Chet was sent to the penalty box
Pat is the current player
They have rolled a 1
Pat's new location is 6
The category is Sports
Sports Question 2
Answer was corrent!!!!
Pat now has 2 Gold Coins.
Sue is the current player
They have rolled a 4
Sue's new location is 6
The category is Sports
Sports Question 3
Answer was corrent!!!!
Sue now has 2 Gold Coins.
Chet is the current player
They have rolled a 5
Chet is getting out of the penalty box
Chet's new location is 11
The category is Rock
Rock Question 0
Answer was correct!!!!
Chet now has 2 Gold Coins.
Pat is the current player
They have rolled a 3
Pat's new location is 9
The category is Science
Science Question 1
Answer was corrent!!!!
Pat now has 3 Gold Coins.
Sue is the current player
They have rolled a 1
Sue's new location is 7
The category is Rock
Rock Question 1
Answer was corrent!!!!
Sue now has 3 Gold Coins.
Chet is the current player
They have rolled a 4
Chet is not getting out of the penalty box
Pat is the current player
They have rolled a 4
Pat's new location is 1
The category is Science
Science Question 2
Answer was corrent!!!!
Pat now has 4 Gold Coins.
Sue is the current player
They have rolled a 3
Sue's new location is 10
The category is Sports
Sports Question 4
Answer was corrent!!!!
Sue now has 4 Gold Coins.
Chet is the current player
They have rolled a 4
Chet is not getting out of the penalty box
Pat is the current player
They have rolled a 5
Pat's new location is 6
The category is Sports
Sports Question 5
Question was incorrectly answered
Pat was sent to the penalty box
Sue is the current player
They have rolled a 4
Sue's new location is 2
The category is Sports
Sports Question 6
Answer was corrent!!!!
Sue now has 5 Gold Coins.
Chet is the current player
They have rolled a 1
Chet is getting out of the penalty box
Chet's new location is 0
The category is Pop
Pop Question 1
Answer was correct!!!!
Chet now has 3 Gold Coins.
Pat is the current player
They have rolled a 5
Pat is getting out of the penalty box
Pat's new location is 11
The category is Rock
Rock Question 2
Answer was correct!!!!
Pat now has 5 Gold Coins.
Sue is the current player
They have rolled a 4
Sue's new location is 6
The category is Sports
Sports Question 7
Answer was corrent!!!!
Sue now has 6 Gold Coins.
```

Notre code s'exécute bien et produit des résultats.

Analysons-le : 
* Nous avons 3 joueurs : Chet, Pat et Sue
* Chaque joueur a un tour de jeu 
* Chaque joueur lance un dé et avance de la valeur du dé
* Chaque joueur répond à une question
* Si la réponse est correcte, le joueur gagne une pièce d'or
* Si la réponse est incorrecte, le joueur est envoyé sur le banc des pénalités (penalty box)
* La logique pour faire sortir un joueur du banc des pénalités n'est pas très claire
* Le jeu se termine lorsqu'un joueur a 6 pièces d'or

## Rentrons un peu plus dans le code

Les fichiers sont mal formatés : utiliser l'IDE pour les formater afin d'améliorer leur lisibilité.

Vous pouvez également utiliser [php-cs-fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) pour aller plus loin (avec notamment l'utilisation du typage strict).

### GameRunner.php

Nous pouvons facilement identifier certaines particularités : 
* Nous appelons la méthode `roll` de `Game` pour lancer le dé
* Selon des valeurs aléatoires, nous indiquons si le joueur répond correctement ou non à la question
* Nous affectons le résultat des méthodes `wrongAnswer` et `wasCorrectlyAnswered` à une variable `$notAWinner`
* Nous bouclons tant que cette variable est vraie.

### Game.php

Ce fichier est beaucoup plus touffu.
Certaines méthodes sont plus complexes que d'autres avec plusieurs niveaux d'indentations et il est difficile de comprendre la logique qui y est effectuée.

## Golden master

Lorsque l'on travaille sur un code legacy, il peut être difficile de le modifier.
Nous remarquons que le code est difficile à comprendre, il faut donc trouver une autre approche en le testant.

Au lieu d'essayer de comprendre quoi tester, nous pouvons tester le jeu dans son intégralité beaucoup de fois.
Cela produira de nombreux résultats suffisamment variés pour couvrir tous les cas de figure.

Il est recommandé d'exécuter le code au moins 10 000 fois mais nous pouvons le faire 2 fois plus.

### Ecrire le générateur de jeu de données

Créons un fichier de test `tests\GolderMasterTest.php` afin de générer un premier jeu de données.

```php
declare(strict_types=1);

namespace ConveyCode\Tests;

use PHPUnit\Framework\TestCase;

final class GoldenMasterTest extends TestCase
{
    public function testGenerateOutput(): void
    {
        ob_start();
        require_once __DIR__ . '/../src/Trivia/GameRunner.php';
        $output = ob_get_clean();
        var_dump($output);
    }
}
```

En exécutant le test une première fois, nous obtenons un résultat : 

```bash
./vendor/bin/phpunit
PHPUnit 10.5.39 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.1.2-1ubuntu2.19
Configuration: /mnt/e/project/github/trivia-php/phpunit.xml

string(2700) "Chet was added
They are player number 1
Pat was added
They are player number 2
Sue was added
They are player number 3
Chet is the current player
They have rolled a 3
Chet's new location is 3
The category is Rock
Rock Question 0
Answer was corrent!!!!
```

En l'exécutant une seconde fois, nous pouvons observer un comportement anormal : 

```bash
./vendor/bin/phpunit
PHPUnit 10.5.39 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.1.2-1ubuntu2.19
Configuration: /mnt/e/project/github/trivia-php/phpunit.xml

string(2703) "Chet was added
They are player number 1
Pat was added
They are player number 2
Sue was added
They are player number 3
Chet is the current player
They have rolled a 6
Chet's new location is 6
The category is Sports
Sports Question 0
Answer was corrent!!!!
```

Les lancées de dés et les catégories sont différents !!

### Le générateur de nombre

```php
do {
    $aGame->roll(rand(0, 5) + 1);

    if (rand(0, 9) == 7) {
        $notAWinner = $aGame->wrongAnswer();
    } else {
        $notAWinner = $aGame->wasCorrectlyAnswered();
    }
} while ($notAWinner);
```

La fonction `rand` génère un nombre aléatoire et ainsi des résultats différents d'une exécution à l'autre. 
PHP nous fournit une solution via une autre fonction : [`srand` ou `mt_srand`](https://www.php.net/manual/fr/function.mt-srand.php).

```php
public function testGenerateOutput(): void
{
    ob_start();
    mt_srand(1);
    require_once __DIR__ . '/../src/Trivia/GameRunner.php';
    $output = ob_get_clean();
    var_dump($output);
}
```

En mettant `mt_srand(1)` avant l'exécution du jeu, nous obtenons toujours le même résultat.

### Mettons le résultat dans un fichier

```php
 public function testGenerateOutput(): void
    {
        file_put_contents('/tmp/gm.txt', $this->generateOutput());
        $expectedContent = file_get_contents('/tmp/gm.txt');
        $this->assertSame($expectedContent, $this->generateOutput());
    }

    private function generateOutput(): string
    {
        ob_start();
        mt_srand(1);
        require_once __DIR__ . '/../src/Trivia/GameRunner.php';
        return ob_get_clean();
    }
```

Nous avons extrait le code dans une méthode privée et effectué un premier test d'égalité.
Seulement, le test échoue car la fonction `require_once` retourne une chaine vide lors de la 2ème exécution de la méthode.

En changeant `require_once` par `require`, le test passe.

```
./vendor/bin/phpunit
PHPUnit 10.5.39 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.1.2-1ubuntu2.19
Configuration: /mnt/e/project/github/trivia-php/phpunit.xml

.                                                                   1 / 1 (100%)

Time: 00:00.158, Memory: 8.00 MB

OK (1 test, 1 assertion)
```

### Exécutons le plusieurs fois

```php
public function testGenerateOutput(): void
{
    $this->generateManyOutputs(20, '/tmp/gm.txt');
    $this->generateManyOutputs(20, '/tmp/gm2.txt');
    $expectedContent = file_get_contents('/tmp/gm.txt');
    $actualContent = file_get_contents('/tmp/gm2.txt');

    $this->assertSame($expectedContent, $actualContent);
}

 private function generateManyOutputs(int $times, string $filename): void
{
    $first = true;
    for ($time = 0; $time < $times; $time++) {
        if ($first) {
            file_put_contents($filename, $this->generateOutput());
            $first = false;
        } else {
            file_put_contents($filename, $this->generateOutput(), FILE_APPEND);
        }
    }
}
```

Nous avons créé une nouvelle méthode `generateMany` qui génère plusieurs fichiers de jeu de données ou sein d'un même fichier.

Par contre, les résultats sont toujours identiques avec à chaque fois le même joueur qui gagne.

```bash
cat /tmp/gm.txt | grep "has 6 Gold Coins."
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
```

Cela provient ne notre fonction `mt_srand(1)` qui fixe le générateur de nombre aléatoire à 1.

### Exécutons le jeu différemment

Ajoutons un paramètre à notre méthode `generateOutput` pour passer un seed.

```php
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
```

Maintenant les résultats sont différents : 

```bash
cat /tmp/gm.txt | grep "has 6 Gold Coins."
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Pat now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Sue now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Pat now has 6 Gold Coins.
Sue now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Pat now has 6 Gold Coins.
Pat now has 6 Gold Coins.
Chet now has 6 Gold Coins.
Pat now has 6 Gold Coins.
```

## Allons plus loin

```php
public function testGenerateOutput(): void
{
    $times = 20000;
    $this->generateManyOutputs($times, '/tmp/gm.txt');
    $this->generateManyOutputs($times, '/tmp/gm2.txt');
    $expectedContent = file_get_contents('/tmp/gm.txt');
    $actualContent = file_get_contents('/tmp/gm2.txt');

    $this->assertSame($expectedContent, $actualContent);
}
```

Si vous avez des problèmes de mémoire, changez l'assertion `$this->assertSame($expectedContent, $actualContent);` par `  $this->assertTrue($expectedContent === $actualContent);`
