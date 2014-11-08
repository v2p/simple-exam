<?php

class Answer implements JsonSerializable
{
    private $id;

    private $name;

    private $isCorrect;

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'isCorrect' => $this->isCorrect,
        ];
    }
}

class Question implements JsonSerializable
{
    private $id;

    private $answers;

    public function __construct($index)
    {
        $this->id = $index;
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
            'id' => $this->id,
            'answers' => $this->answers,
        ];
    }
}

$allQuestions = range(1, 504);
$selectedQuestionsIndexes = array_rand($allQuestions, 40);

$selectedQuestions = [];
foreach ($selectedQuestionsIndexes as $index) {
    $selectedQuestions[] = new Question($allQuestions[$index]);
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
            </div>
        </div>
    </nav>

    <div class="row">
        <div class="col-xs-9">
            <div class="questions-container">
                <?php
                /** @var Question $question */
                foreach ($selectedQuestions as $question) { ?>
                <div class="panel panel-default" id="<?= $question->getAnchor() ?>">
                    <div class="panel-heading"><?= $question->getName() ?></div>
                    <div class="panel-body">
                        <img src="<?= $question->getImage() ?>" alt="Загрузка..."/>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>