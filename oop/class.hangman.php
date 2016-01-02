<?php
/***
* File: oop/class.hangman.php
* Author: design1online.com, LLC
* Created: 5.11.2011
* License: Public GNU
***/

class Hangman extends Game {

    var $guesses;                   //int - maximum guesses per word
    var $letters = array();         //array - letters guessed so far
    var $word_index;                //int - index of the current word
    var $word_letters = array();    //array - array of the letters in the word
    var $word_list = array();       //array - list of words they can guess
    
    var $alphabet = array(      //array - all letters in the alphabet
        "a", "b", "c", "d", "e", "f", "g", "h",
        "i", "j", "k", "l", "m", "n", "o", "p", 
        "q", "r", "s", "t", "u", "v", "w", "x", 
        "y", "z" );
        
    var $punctuation = array(   //array - punctuation marks in the word list
        " ", "\,", "\'", "\"", "\/", "\\", "\[",
        "\]", "\+", "\-", "\_", "\=", "\!", "\~", 
        "\~", "\@", "\.", "\?", "\$", "\#", "\%", 
        "\^", "\&", "\*", "\( ", "\ )", "\{", "\}",
        "\|", "\:", "\;" );

    /**
    * Purpose: default constructor
    * Preconditions: none
    * Postconditions: parent object started
    * Change to use __construct method?
    **/
    function hangman() {
        /**
        * instantiate the parent game class so this class
        * inherits all of the game class's attributes 
        * and methods
        **/
        game::start();
    }
    
    /**
    * Purpose: start a new hangman game
    * Preconditions: maximum number of guesses
    * Postconditions: game is ready to be displayed
    * Why isn't this using $guesses?
    **/
    function new_game( $max_guesses = 5 ) {
        //setup the game
        $this->start();
        
        //make sure we clear out the last letters they guessed
        $this->letters = array();
            
        //set how many guesses they get before it's a game over
        if ( $max_guesses )
            $this->set_guesses( $max_guesses );
            
        //pick a word for them to try and guess
        $this->set_word();
    }
    
    /**
    * Purpose: set or retrieve maximum guesses before game over
    * Preconditions: 
    * Postconditions: 
    **/
    function play_game( $_POST ) {
        //player pressed the button to start a new game
        if ( $_POST['newgame'] || empty( $this->word_list ) )
            $this->new_game();
            
        //player is trying to guess a letter
        if ( !$this->isOver() && $_POST['letter'] )
            echo $this->guess_letter( $_POST['letter'] );
                
        //display the game
        $this->display_game();
    }
    
    /**
    * Purpose: set or retrieve maximum guesses they can make
    * Preconditions: amount of guesses (optional)
    * Postconditions: guesses has been updated
    **/
    function set_guesses( $amount = 0 ) {       
        $this->guesses += $amount;
    }
    
    /**
    * Purpose: display the game interface
    * Preconditions: none
    * Postconditions: start a game or keep playing the current game
    **/
    function display_game() {
        //while the game isn't over
        if ( !$this->isOver() )
        {
            echo "<div id=\"picture\">" . $this->picture() . "</div>
                  <div id=\"guess_word\">" . $this->solved_word() . "</div>
                  <div id=\"select_letter\">
                    Enter A Letter:
                        <input type=\"text\" name=\"letter\" value=\"\" size=\"2\" maxlength=\"1\" />
                        <input type=\"submit\" name=\"submit\" value=\"Guess\" />
                  </div>";
                  
                  if ( !empty( $this->letters ) )
                    echo "<div id=\"guessed_letters\">Letters Guessed: " . implode( $this->letters, ", " ) . "</div>";
        }
        else
        {
            //they've won the game
            if ( $this->won )
                echo success_message( "Congratulations! You've won the game.<br/>
                                Your final score was: $this->score" );
            else if ( $this->health < 0 )
            {
                echo error_message( "Game Over! Good try.<br/>
                                Your final score was: $this->score" );

                echo "<div id=\"picture\">" . $this->picture() . "</div>";
            }

            echo "<div id=\"start_game\"><input type=\"submit\" name=\"newgame\" value=\"New Game\" /></div>";
        }
    }
    
    /**
    * Purpose: guess a letter in this word
    * Preconditions: a game has started
    * Postconditions: the game data is updated
    **/
    function guess_letter( $letter ) {          

        if ( $this->isOver() )
            return;

        if ( !is_string( $letter ) || strlen( $letter ) != 1 || !$this->is_letter( $letter ) )
            return error_message( "Oops! Please enter a letter." );
            
        //check if they've already guessed the letter
        if ( in_array( $letter, $this->letters ) )
            return error_message( "Oops! You've already guessed this letter." );
            
        //only allow lowercase letters
        $letter = strtolower( $letter );
            
        //if the word contains this letter
        if ( !( strpos( $this->word_list[$this->word_index], $letter ) === false ) )
        {
            //increase their score based on how many guesses they've used so far
            if ( $this->health > ( 100/ceil( $this->guesses/5 ) ) )
                $this->setScore( 5 );
            else if ( $this->health > ( 100/ceil( $this->guesses/4 ) ) )
                $this->setScore( 4 );
            else if ( $this->health > ( 100/ceil( $this->guesses/3 ) ) )
                $this->setScore( 3 );
            else if ( $this->health > ( 100/ceil( $this->guesses/2 ) ) )
                $this->setScore( 2 );
            else
                $this->setScore( 1 );
                
            //add the letter to the letters array
            array_push( $this->letters, $letter );
            
            //if they've found all the letters in this word
            if ( implode( array_intersect( $this->word_letters, $this->letters ), "" ) == 
                str_replace( $this->punctuation, "", strtolower( $this->word_list[$this->word_index] ) ) )
                $this->won = true;
            else
                return success_message( "Good guess, that's correct!" );
        }
        else //word doesn't contain the letter
        {
        
            //reduce their health
            $this->setHealth( ceil( 100/$this->guesses ) * -1 );
            
            //add the letter to the letters array
            array_push( $this->letters, $letter );
            
            if ( $this->isOver() )
                return;
            else
                return error_message( "There are no letter $letter's in this word." );
        }
    }
    
    /**
    * Purpose: pick a random word to try and solve
    * Preconditions: none
    * Postconditions: if the word exists a word index has been set
    **/
    function set_word() {
        //if the word list is empty we need to load it first
        if ( empty( $this->word_list ) )
            $this->load_words();
    
        //reset the word index to a new word
        if ( !empty( $this->word_list ) )
            $this->word_index = rand( 0, count( $this->word_list )-1 );
            
        //convert the string to an array we can use
        $this->word_to_array();
    }
    
    /**
    * Purpose: load the words from the config file into an array
    * Preconditions: filename to load the words from (optional)
    * Postconditions: the word list has been loaded
    **/
    function load_words( $filename = "config/wordlist.txt" ) {
        if ( file_exists( $filename ) ) {
            $fstream = fopen( $filename, "r" );
            while ( $word = fscanf( $fstream, "%s %s %s %s %s %s %s %s %s %s\n" ) ) {

                $phrase = "";

                if ( is_string( $word[0] ) )
                {
                    foreach ( $word as $value )
                        $phrase .= $value . " ";

                    array_push( $this->word_list, trim( $phrase ) );
                }
            }
        }
    }
    
    /**
    * Purpose: return the image that should be displayed with this number of wrong guesses
    * Preconditions: none
    * Postconditions: picture returned
    **/
    function picture() {
        $count = 1;

        for ( $i = 100; $i >= 0; $i-= ceil( 100/$this->guesses ) )
        {
            if ( $this->health == $i )
            {
                if ( file_exists( "images/" . ( $count-1 ) . ".jpg" ) )
                    return "<img src=\"images/" . ( $count-1 ) . ".jpg\" alt=\"Hangman\" title=\"Hangman\" />";
                else
                    return "ERROR: images/" . ( $count-1 ) . ".jpg is missing from the hangman images folder.";
            }
                
            $count++;
        }
        
        return "<img src=\"images/" . ( $count-1 ) . ".jpg\" alt=\"Hangman\" title=\"Hangman\" />";
    }
    
    /**
    * Purpose: display the part of the word they've solved so far
    * Preconditions: a word has been set using set_word()
    * Postconditions: the letters they've guessed correctly show up
    **/
    function solved_word() {

        $result = "";
        
        for ( $i = 0; $i < count( $this->word_letters ); $i++ )
        {
            $found = false;
            
            foreach( $this->letters as $letter ) {
                if ( $letter == $this->word_letters[$i] )
                {
                    $result .= $this->word_letters[$i]; //they've guessed this letter
                    $found = true;
                }
            }
            
            if ( !$found && $this->is_letter( $this->word_letters[$i] ) )
                $result .= "_"; //they haven't guessed this letter
                
            else if ( !$found ) //this is a space or non-alpha character
            {
                //make spaces more noticable
                if ( $this->word_letters[$i] == " " )
                    $result .= "&nbsp;&nbsp;&nbsp;";
                else
                    $result .= $this->word_letters[$i];
            }
        }
    
        return $result;
    }
    
    /**
    * Purpose: convert the selected word to an array
    * Preconditions: a word has been selected
    * Postconditions: word_letters now contains an array representation of the 
    *   selected word
    **/
    function word_to_array() {
        $this->word_letters = array(); 
        
        for ( $i = 0; $i < strlen( $this->word_list[$this->word_index] ); $i++ )
            array_push( $this->word_letters, $this->word_list[$this->word_index][$i] );
    }
    
    /**
    * Purpose: check to see if this value is a letter
    * Preconditions: value to check
    * Postconditions: returns true if letter found
    **/
    function is_letter( $value ) {
        if ( in_array( $value, $this->alphabet ) )
            return true;
            
        return false;
    }
}