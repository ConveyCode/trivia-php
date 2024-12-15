# Magic Strings

Une magic string est une chaîne de caractères qui est utilisée directement dans le code source sans être déclarée.

La signification de ces chaînes de caractères peut être évidente pour l'auteur du code, mais cela peut devenir plus compliqué plusieurs mois après le développement.
D'autre part, on peut se retrouver avec de la duplication ce qui peut rendre le code source plus difficile à maintenir.

## Changeons nos tests

Avant de changer notre code, adaptons nos tests afin de générer les résultats qu'une seule fois.

```php
private const GM_PATH = __DIR__ .  '/gm.txt';

public function testGenerateOutput(): void
{
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
```

Après la première exécution, nous pouvons désactiver le test `testGenerateOutput` : 
* soit en le commentant
* soit en le supprimant
* soit via la méthode `markTestSkipped` de PHPUnit

```php
public function testGenerateOutput(): void
{
    $this->markTestSkipped('This test is only for generating the golden master');
    $times = 20000;
    $this->generateManyOutputs($times, self::GM_PATH);
}
```

Cela nous permettra de toujours nous appuyer sur le résultat original et de s'assurer que nous n'avons pas de régression qui pourrait comparer un résultat identique mais incorrect.

## Faisons nos premiers changements

Le constructeur de notre classe `Game`, possède une méthode `createRockQuestion` dont le contenu est identique aux autres lignes présentes dans le constructeur.
Elle n'apporte donc aucune valeur, remplaçons son appel par son contenu en utilisant la technique de refactoring [Inline Method](https://refactoring.guru/fr/inline-method) :
* Vérifier que la méthode n'est pas redéfinie ailleurs
* Trouver tous les appels et les remplacer par son contenu
* Supprimer la méthode

```php
public function __construct()
{
    $this->players = [];
    $this->places = [0];
    $this->purses = [0];
    $this->inPenaltyBox = [0];
    $this->popQuestions = [];
    $this->scienceQuestions = [];
    $this->sportsQuestions = [];
    $this->rockQuestions = [];
    for ($i = 0; $i < 50; $i++) {
        array_push($this->popQuestions, "Pop Question " . $i);
        array_push($this->scienceQuestions, ("Science Question " . $i));
        array_push($this->sportsQuestions, ("Sports Question " . $i));
        array_push($this->rockQuestions, "Rock Question " . $i);
    }
}
```

En relançant les tests, nous nous assurons que nous n'avons pas oublié un appel.

## Introduce Local Variable

La méthode `currentCategory` contient beaucoup de chaine de caractères en dur et dupliqués.
Nous pouvons extraire les valeurs dans des variables pour éviter cela.

```php
public function currentCategory()
{
    $popCategory = "Pop";
    $scienceCategory = "Science";
    $sportCategory = "Sports";
    $rockCategory = "Rock";

    if ($this->places[$this->currentPlayer] == 0) {
        return $popCategory;
    }

    if ($this->places[$this->currentPlayer] == 4) {
        return $popCategory;
    }

    if ($this->places[$this->currentPlayer] == 8) {
        return $popCategory;
    }

    if ($this->places[$this->currentPlayer] == 1) {
        return $scienceCategory;
    }

    if ($this->places[$this->currentPlayer] == 5) {
        return $scienceCategory;
    }

    if ($this->places[$this->currentPlayer] == 9) {
        return $scienceCategory;
    }

    if ($this->places[$this->currentPlayer] == 2) {
        return $sportCategory;
    }

    if ($this->places[$this->currentPlayer] == 6) {
        return $sportCategory;
    }

    if ($this->places[$this->currentPlayer] == 10) {
        return $sportCategory;
    }

    return $rockCategory;
}
```

Vous voyez certainement d'autres améliorations à apporter et il est tentant de tout refactorer d'un coup mais cela peut être dangereux.
Vous pouvez vous retrouver dans une impasse et finir par perdre plus de temps.
Autant y aller par babysteps et s'assurer que les tests passent à chaque étape.

# Magic Numbers

C'est la même chose que les magic strings mais avec des nombres.

## La classe `GameRunner`

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

Les premiers nombres magiques avec la fonction `roll` utilisent un chiffre aléatoire entre 1 et 6.
Nous pouvons déporter cette logique dans une variable :

```php
$dice = rand(0, 5) + 1;
$aGame->roll($dice);
```

La 2ème expression `rand(0, 9) == 7` est plus complexe.
Si le résultat de la fonction `rand` est égal à 7, alors il semble que le joueur a donné une mauvaise réponse.
Nous pouvons donc déporter le chiffre 7 dans une variable : 

```php
$wrongAnswerId = 7;
if (rand(0, 9) == $wrongAnswerId) {
    $notAWinner = $aGame->wrongAnswer();
} else {
    $notAWinner = $aGame->wasCorrectlyAnswered();
}
```

Pour les 2 autres valeurs, nous pouvons imaginer que le 0 et le 9 sont des intervalles.
Il pourrait être intéressant de les définir comme ceux-ci :

```php
$minAnswerId = 0;
$maxAnswerId = 9;
$wrongAnswerId = 7;

if (rand($minAnswerId, $maxAnswerId) == $wrongAnswerId) {
    $notAWinner = $aGame->wrongAnswer();
} else {
    $notAWinner = $aGame->wasCorrectlyAnswered();
}
```

Le résultat final devrait ressembler à ça : 

```php
do {
    $dice = rand(0, 5) + 1;
    $aGame->roll($dice);

    $minAnswerId = 0;
    $maxAnswerId = 9;
    $wrongAnswerId = 7;

    if (rand($minAnswerId, $maxAnswerId) == $wrongAnswerId) {
        $notAWinner = $aGame->wrongAnswer();
    } else {
        $notAWinner = $aGame->wasCorrectlyAnswered();
    }
} while ($notAWinner);
```
Comme nous n'avons pas besoin de définir ces valeurs à chaque itération du `do/while`, autant les déporter plus haut : 

```php
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
```
## La classe `Game`

Nous avons également des nombres magiques dans la classe `Game`.

```php
for ($i = 0; $i < 50; $i++) {
    array_push($this->popQuestions, "Pop Question " . $i);
    array_push($this->scienceQuestions, ("Science Question " . $i));
    array_push($this->sportsQuestions, ("Sports Question " . $i));
    array_push($this->rockQuestions, "Rock Question " . $i);
}
```

Ici, nous pouvons comprendre que nous ajoutons des questions dans 4 listes de catégories différentes.
Le 50 doit représenter la taille de ces listes.
Nous pouvons continuer avec le concept d'`Introduce Local Variable` et définir une variable `$categorySize` :

```php
$categorySize = 50;
for ($i = 0; $i < $categorySize; $i++) {
    array_push($this->popQuestions, "Pop Question " . $i);
    array_push($this->scienceQuestions, ("Science Question " . $i));
    array_push($this->sportsQuestions, ("Sports Question " . $i));
    array_push($this->rockQuestions, "Rock Question " . $i);
}
```

Le 2ème nombre magique se trouve dans une méthode qui est utilisée : 

```php
 public function isPlayable()
{
    return ($this->howManyPlayers() >= 2);
}
```

Méthode inutilisée = code mort, autant la supprimer.

### La méthode `roll`

Il y a plusieurs chiffres à variabiliser ici : 
    
```php
if ($roll % 2 != 0) {
```

Ce if vérifie que le nombre est impaire, créons une variable :

```php
$isOdd = $roll % 2 != 0;
if ($isOdd) {
```

Le 11 et 12 parait plus complexe : 

```php
if ($this->places[$this->currentPlayer] > 11) {
    $this->places[$this->currentPlayer] = $this->places[$this->currentPlayer] - 12;
}
```

Si la position actuelle du joueur est supérieure à 11, alors on retire 12.
Cela semble ressembler au cas où le joueur arrive à la fin du plateau de jeu et doit revenir au début.
12 est donc la taille du plateau et 11 la dernière position en partant de 0;

```php
$lastPositionOnTheBoard = 11;
$boardSize = 12;
if ($this->inPenaltyBox[$this->currentPlayer]) {
    $isOdd = $roll % 2 != 0;
    if ($isOdd) {
        ...
        if ($this->places[$this->currentPlayer] > $lastPositionOnTheBoard) {
            $this->places[$this->currentPlayer] = $this->places[$this->currentPlayer] - $boardSize;
        }
        ...
    } else {
        $this->places[$this->currentPlayer] = $this->places[$this->currentPlayer] + $roll;
        if ($this->places[$this->currentPlayer] > $lastPositionOnTheBoard) {
            $this->places[$this->currentPlayer] = $this->places[$this->currentPlayer] - $boardSize;
        }
        ...
    } 
}
```

## La dernière méthode

Il reste une méthode avec un nombre magique : 

```php
public function didPlayerWin()
{
    return !($this->purses[$this->currentPlayer] == 6);
}
```

On comprend que 6 est le nombre de pièces pour gagner  : 

```php
