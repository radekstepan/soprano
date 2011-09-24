<?php

include('../lib/soprano.php');

class Application extends Soprano {

    public function getCreate() {
        $hangman = Factory::build('Hangman', 'new');
        $this->template->hangman = $hangman;
        
        $this->template->render('create');
    }

    public function getGuess($letter) {
        $hangman = Factory::build('Hangman');
        $this->template->hangman = $hangman;
        
        if ($hangman->guess(strtoupper($letter))) {
            $this->template->render('winner');
        }

        $this->template->render('guess');
    }

}

$app = new Application();

$app->get('/', 'getCreate');
$app->get('/create', 'getCreate');
$app->get('/guess/:letter', 'getGuess');

$app->run();