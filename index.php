<?php
/***
* File: index.php
* Author: design1online.com, LLC
* Created: 5.11.2011
* License: Public GNU
***/

//include the required files
require_once('oop/class.game.php');
require_once('oop/class.hangman.php');

//this will keep the game data as they refresh the page
session_start();

//if they haven't started a game yet let's load one
if (!$_SESSION['game']['hangman'])
	$_SESSION['game']['hangman'] = new hangman();

?>
<html>
	<head>
		<title>Hangman</title>
		<link rel="stylesheet" type="text/css" href="inc/style.css" />
	</head>
	<body>
		<div id="content">
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
		<h2>Let's Play Hangman!</h2>
		<?php
			$_SESSION['game']['hangman']->playGame($_POST);
		?>
		</form>
		</div>
	</body>
</html>