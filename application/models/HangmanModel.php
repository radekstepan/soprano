<?php

class Hangman {

    private
        $word,
        $guessed,
        $available,
        $left,
        $round,
        $definition;

    public function startNewGame() {
        // parse a random word
        $words = file_get_contents(APP_DIR . '/models/words.txt');
        $words = explode("\n", $words);
        $this->word = strtoupper(trim($words[rand(0, count($words))]));
        
        // initialize our word to guess
        $this->guessed = array();
        foreach (str_split($this->word) as $letter) {
            $this->guessed[] = array($letter => '_');
        }
        $this->left = $this->getLength();
        $this->round = 1;
        $this->available = range('a', 'z');
    }

    /**
     * Make a guess.
     * @param  $letter
     * @return bool true if we have won
     */
    public function guess($letter) {
        if (empty($this->guessed)) Soprano::exception('You need to create a new game first!');
        
        foreach ($this->guessed as &$g) {
            // if we have a match and are not guessing the guessed again...
            if (key($g) == $letter && current($g) == '_') {
                $g = array($letter => $letter);
                $this->left--;
            }
        }
        $this->removeLetter(strtolower($letter));
        $this->round++;
        return ($this->left <= 0);
    }

    private function removeLetter($letter) {
        foreach ($this->available as $key => $value) {
            if ($letter == $value) unset($this->available[$key]);
        }
    }

    public function getDefinition() {
        if (empty($this->definitions)) {
            $wordnik = Factory::build('Wordnik');
            $definitions = $wordnik->getDefinitions(strtolower($this->word));
            if (!is_null($definitions)) {
                foreach ($definitions as $definition) {
                    if (isset($definition->text)) {
                        $this->definition = str_ireplace($this->word, '[WORD]', $definition->text);
                        break;
                    }
                }
            }
        }
        return $this->definition;
    }

    public function getLength() {
        return strlen($this->word);
    }

    public function getGuessed() {
        return $this->guessed;
    }

    public function getRound() {
        return $this->round;
    }

    public function getAvailable() {
        return $this->available;
    }

}
