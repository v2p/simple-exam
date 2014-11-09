<?php

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
    private static $questionsCount = 0;

    private $id;
    private $sequentalIndex;

    /**
     * @var Answer[]
     */
    private $answers = [];

    public function __construct($id)
    {
        $this->id = $id;
        $this->sequentalIndex = ++self::$questionsCount;
    }

    public function addAnswer(Answer $answer)
    {
        $this->answers[] = $answer;
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
        return 'Вопрос #' . $this->sequentalIndex;
    }

    public function getId()
    {
        return $this->id;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'image' => $this->getImage(),
            'name' => $this->getName(),

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
            padding-top: 10px;
        }

        .answers {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        .answers label {
            font-weight: 500;
        }

        .result-panel, .questions-container {
            margin-top: 20px;
        }
    </style>

    <script src="res/js/jquery-1.11.1.min.js"></script>
    <script src="res/js/bootstrap.min.js"></script>
    <script src="res/js/knockout-3.2.0.js"></script>

    <script>
        function Answer(arg) {
            var self = this,
                data = arg || {};

            self.name = data.name;
            self.isCorrect = data.isCorrect || false;
        }

        function Question(arg) {
            var self = this,
                data = arg || {};

            self.id = data.id;
            self.name = data.name;
            self.image = data.image;

            self.answers = ko.observableArray();

            $.each(data.answers, function(key, answerData) {
                self.answers.push(new Answer(answerData));
            });

            self.selectedAnswer = ko.observable(null);

            self.isCorrect = ko.computed(function() {
                var answer = self.selectedAnswer();

                if (!answer) {
                    return false;
                }

                return answer.isCorrect;
            });
        }

        function ViewModel(arg) {
            var self = this,
                data = arg || {};

            self.questions = data.questions || [];
            self.selectedQuestion = ko.observable(self.questions[0]);
            self.resultShowed = ko.observable(false);

            self.previousQuestion = function() {
                var index = self.questions.indexOf(self.selectedQuestion());

                if (index == 0) {
                    index = self.questions.length - 1;
                } else {
                    index--;
                }

                self.selectedQuestion(self.questions[index]);
            };

            self.nextQuestion = function() {
                var index = self.questions.indexOf(self.selectedQuestion());

                if (index == self.questions.length - 1) {
                    index = 0;
                } else {
                    index++;
                }

                self.selectedQuestion(self.questions[index]);
            };

            self.goToQuestion = function(question) {
                self.selectedQuestion(question);
            };

            self.toggleShowResult = function() {
                self.resultShowed(!self.resultShowed());
            };

            self.panelClass = function(question) {
                if (!self.resultShowed()) {
                    return 'panel-default';
                }

                if (question.isCorrect()) {
                    return 'panel-success';
                } else {
                    return 'panel-danger';
                }
            };

            self.toolbarClass = function(question) {
                var result = [];

                if (self.selectedQuestion() == question) {
                    result.push('active');
                }

                if (self.resultShowed()) {
                    if (question.isCorrect()) {
                        result.push('btn-success');
                    } else {
                        result.push('btn-danger');
                    }
                } else {
                    result.push('btn-default');
                }

                return result.join(' ');
            };

            self.correctAnswers = ko.computed(function() {
                var correctCount = 0;
                $.each(self.questions, function(index, question) {
                    if (question.isCorrect()) {
                        correctCount++;
                    }
                });

                return correctCount;
            });

            self.incorrectAnswers = ko.computed(function() {
                return self.questions.length - self.correctAnswers();
            });
        }

        var exports = <?= json_encode($selectedQuestions); ?>

        $(document).ready(function() {

            var questions = [];
            $.each(exports, function(key, questionData) {
                questions.push(new Question(questionData));
            });

            var viewModel = new ViewModel({questions: questions});
            ko.applyBindings(viewModel);

            $(document).keydown(function(e){
                if (e.keyCode == 37) { // left
                    viewModel.previousQuestion();
                    return false;
                }

                if (e.keyCode == 39) { // right
                    viewModel.nextQuestion();
                    return false;
                }
            });
        });
    </script>
</head>
<body data-spy="scroll" data-target=".questions-list">
<div class="container">
    <div class="row">
        <div class="col-xs-11 col-xs-offset-1" >
            <div class="btn-toolbar" role="toolbar">
                <div class="btn-group btn-group-xs" data-bind="foreach: questions">
                    <button type="button" class="btn btn-default" data-bind="text: $index() + 1, css: $root.toolbarClass($data), click: $root.goToQuestion"></button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-8 col-xs-offset-2">
            <div class="questions-container" data-bind="foreach: questions">
                <div class="panel" data-bind="attr: { id: $data.anchor }, css: $root.panelClass($data), visible: $root.selectedQuestion() == $data">
                    <div class="panel-heading"><span data-bind="html: $data.name"></span></div>
                    <div class="panel-body">
                        <div>
                            <img src="" data-bind="attr: { src: $data.image }" alt="Загрузка..."/>
                        </div>
                        <div class="answers" data-bind="foreach: $data.answers">
                            <div>
                                <label>
                                    <input type="radio" data-bind="checkedValue: $data, checked: $parent.selectedAnswer" />
                                    <span data-bind="html: $data.name"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-8 col-xs-offset-2">
            <button data-bind="click: previousQuestion" type="button" class="btn btn-default">Предыдущий вопрос</button>
            <button data-bind="click: nextQuestion" type="button" class="btn btn-default">Следующий вопрос</button>

            <div class="pull-right">
                <button data-bind="click: toggleShowResult, css: { active: resultShowed }" type="button" class="btn btn-default">Показать результаты</button>
            </div>
        </div>
    </div>
    <div class="row" data-bind="visible: resultShowed">
        <div class="col-xs-8 col-xs-offset-2">
            <div class="panel panel-info result-panel">
                <div class="panel-heading">Результаты</div>
                <div class="panel-body">
                    <div>
                        Правильно:
                        <span data-bind="text: correctAnswers"></span>
                    </div>
                    <div>
                        Неправильно:
                        <span data-bind="text: incorrectAnswers"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>