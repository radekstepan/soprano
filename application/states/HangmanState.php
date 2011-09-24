<?php

class HangmanState extends State {

    public function getObjectName() {
        return 'Hangman';
    }

    public function initialize() {
        $this->startNewGame();
    }

}