<?php

function showFile() {
    $file = array_values(file(__DIR__ . '/res/answers.csv'));

    $result = [];

    while (list($lineNumber, $dirtyLine) = each($file)) {
        $line = trim($dirtyLine);

        if ($line == '') {
            continue;
        }

        if (is_numeric($line)) {
            $valueInLine = (int)$line;

            if ($valueInLine != 1200) {
                $questionIndex = $valueInLine;

                $answerItem = [];
                while (list($answerLineNumber, $answerLine) = each($file)) {
                    $answer = trim($answerLine);
                    if ($answer == '') {
                        break;
                    }

                    if (strrpos($answer, '+') === strlen($answer) - 1) {
                        $answer = trim($answer, '+');
                        $isCorrect = true;
                    } else {
                        $isCorrect = false;
                    }

                    $answerItem[] = [
                        'name' => $answer,
                        'isCorrect' => $isCorrect
                    ];
                }

                $result[$questionIndex] = $answerItem;
            }
        }
    }

    return var_export($result);
}

class Answer implements JsonSerializable
{
    private $name;

    private $isCorrect;

    public function __construct($name, $isCorrect)
    {
        $this->name = $name;
        $this->isCorrect = $isCorrect;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->getName(),
            'isCorrect' => $this->isCorrect,
        ];
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}

class Question implements JsonSerializable
{
    private $id;

    /**
     * @var Answer[]
     */
    private $answers = [];

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function addAnswer(Answer $answer)
    {
        $this->answers[] = $answer;
    }

    /**
     * @return mixed
     */
    public function getAnchor()
    {
        return 'q' . $this->id;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return 'res/images/' . $this->id . '.png';
    }

    public function getName()
    {
        return 'Вопрос #' . $this->id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'answers' => $this->getAnswers(),
        ];
    }

    /**
     * @return Answer[]
     */
    public function getAnswers()
    {
        return $this->answers;
    }
}

$answers = include_once('answers.php');

$allQuestions = range(1, 504);
$selectedQuestionsIndexes = array_rand($allQuestions, 40);

$selectedQuestions = [];
foreach ($selectedQuestionsIndexes as $index) {
    $questionIndex = $allQuestions[$index];
    $question = new Question($questionIndex);

    foreach ($answers[$questionIndex] as $answerData) {
        $question->addAnswer(
            new Answer($answerData['name'], $answerData['isCorrect'])
        );
    }

    $selectedQuestions[] = $question;
}

$questionsByGroups = [
    array_slice($selectedQuestions, 0, 13),
    array_slice($selectedQuestions, 13, 13),
    array_slice($selectedQuestions, 26, 14),
];

?>

<!DOCTYPE html>
<html>
<head lang="ru">
    <meta charset="UTF-8">
    <title>Вопросы к экзамену</title>

    <link rel="stylesheet" href="res/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="res/css/bootstrap-theme.min.css"/>

    <style>
        body {
            padding-top: 70px;
        }

        .answers {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
    </style>

    <script src="res/js/jquery-1.11.1.min.js"></script>
    <script src="res/js/bootstrap.min.js"></script>
    <script src="res/js/knockout-3.2.0.js"></script>

    <script>
        function Answer(arg) {
            var self = this,
                data = arg || {};

            self.id = data.id;
            self.name = data.name;
            self.isCorrect = data.isCorrect || false;
        }

        function Question(arg) {
            var self = this,
                data = arg || {};

            self.id = data.id;
            self.selectedAnswer = ko.observable(null);

            self.isCorrect = ko.computed(function() {
                var answer = self.selectedAnswer();

                if (!answer) {
                    return false;
                }

                return answer.isCorrect;
            });

            self.selectAnswer = function(value) {
                self.selectedAnswer(value);
            };
        }

        jQuery(document).ready(function() {
            console.log(123);
        });
    </script>
</head>
<body data-spy="scroll" data-target=".questions-list">
<div class="container">
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container-fluid">
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <?php foreach($questionsByGroups as $group): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            Вопросы <?= $group[0]->getId(); ?> - <?= $group[count($group) - 1]->getId(); ?>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <?php foreach($group as $question):
                                /** @var Question $question */?>
                            <li>
                                <a href="#<?= $question->getAnchor() ?>">
                                    <?= $question->getName() ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#result">
                            Перейти к результатам
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="row">
        <div class="col-xs-8 col-xs-offset-2">
            <div class="questions-container">
                <?php
                /** @var Question $question */
                foreach ($selectedQuestions as $question) { ?>
                <div class="panel panel-default" id="<?= $question->getAnchor() ?>">
                    <div class="panel-heading"><?= $question->getName() ?></div>
                    <div class="panel-body">
                        <div>
                            <img src="<?= $question->getImage() ?>" alt="Загрузка..."/>
                        </div>
                        <div class="answers">
                            <?php foreach($question->getAnswers() as $answer):
                            /** @var Answer $answer */?>
                            <div>
                                <label>
                                    <input type="radio" /> <?= $answer->getName(); ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <div class="panel panel-primary" id="result">
                <div class="panel-heading">Результаты</div>
                <div class="panel-body">
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>