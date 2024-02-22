<?php
session_start();

function initializeBoard($rows, $cols)
{
    $board = array_fill(0, $rows, array_fill(0, $cols, '?'));

    // Place ships: 2x1, 3x1, and 4x1
    placeShip($board, 2);
    placeShip($board, 3);
    placeShip($board, 4);

    return $board;
}

function placeShip(&$board, $length)
{
    $rows = count($board);
    $cols = count($board[0]);
    $direction = rand(0, 1); // 0 for horizontal, 1 for vertical

    do {
        $startRow = rand(0, $rows - 1);
        $startCol = rand(0, $cols - 1);
    } while (!isValidPosition($board, $startRow, $startCol, $direction, $length));

    if ($direction == 0) { // horizontal
        for ($i = $startCol; $i < $startCol + $length; $i++) {
            $board[$startRow][$i] = 'S';
        }
    } else { // vertical
        for ($i = $startRow; $i < $startRow + $length; $i++) {
            $board[$i][$startCol] = 'S';
        }
    }
}

function isValidPosition($board, $row, $col, $direction, $length)
{
    $rows = count($board);
    $cols = count($board[0]);

    if ($direction == 0) { // horizontal
        if ($col + $length > $cols) {
            return false;
        }
        for ($i = $col; $i < $col + $length; $i++) {
            if ($board[$row][$i] != '?') {
                return false;
            }
        }
    } else { // vertical
        if ($row + $length > $rows) {
            return false;
        }
        for ($i = $row; $i < $row + $length; $i++) {
            if ($board[$i][$col] != '?') {
                return false;
            }
        }
    }
    return true;
}
function processMove(&$board, $row, $col)
{
    if ($board[$row][$col] == 'S' || $board[$row][$col] == 'X') //just in case of refresh page
    {
        $board[$row][$col] = 'X'; // Hit
    } else {
        $board[$row][$col] = 'O'; // Miss
    }
}
function allShipsSunk($board)
{
    foreach ($board as $row) {
        if (in_array('S', $row)) {
            return false;
        }
    }
    return true;
}
function displayBoard($board)
{
    $rows = count($board);
    $cols = count($board[0]);
    echo '<table>';
    for($i = 0; $i < $rows; $i++) {
        echo '<tr>';
        for($j = 0; $j < $cols; $j++) {
            echo '<td>';
            if($board[$i][$j] == 'S' || $board[$i][$j] == '?'){
                //echo '<a href="?name=' . $_GET['name'] . '&move=' . $i . ',' . $j . '">?</a>';
                echo '<form action="battleship.php" method="post">';
                //echo '<input type="hidden" name="name" value="' . htmlspecialchars($_POST['name']) . '">';
                echo '<input type="hidden" name="move" value="' . $i . ',' . $j . '">';
                echo '<input type="submit" value="?">';
                echo '</form>';

            }
            else{
                echo $board[$i][$j];
            }
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}
function displayFinalBoard($board)
{
    $rows = count($board);
    $cols = count($board[0]);
    echo '<table>';
    for($i = 0; $i < $rows; $i++) {
        echo '<tr>';
        for($j = 0; $j < $cols; $j++) {
            echo '<td>';
            if($board[$i][$j] == 'S'){
                echo '?';
            }
            else{
                echo $board[$i][$j];
            }
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}
// Check Reset
if (isset($_POST['reset'])){
    unset($_SESSION['board']);
    unset($_SESSION['moves']);
    unset($_SESSION['user']);
}

// Main logic
if (isset($_POST['name']) || isset($_SESSION['user'])) {
    echo '<link rel="stylesheet" type="text/css" href="styles.css">';
    if (!isset($_SESSION['user'])){
        $_SESSION['user'] = htmlspecialchars($_POST['name']);
    }
    $name = $_SESSION['user'];
    $date = date('Y-m-d H:i:s');
    echo "<p>Hello $name, $date</p>";
    // Handle player's move
    if (isset($_POST['move'])) {
        $move = explode(',', $_POST['move']); // Assuming move is in "row,col" format
        $row = $move[0];
        $col = $move[1];
        $valid = true;
        if ($_SESSION['board'][$row][$col] == 'X' || $_SESSION['board'][$row][$col] == 'O'){
            $valid = false;
        }
        processMove($_SESSION['board'], $row, $col);
        // Decrement remaining moves
        if (isset($_SESSION['moves']) && $valid) {
            $_SESSION['moves']--;
        }
    }
    //if (isset($_POST['reset'])){
  //    unset($_SESSION['board']);
//      unset($_SESSION['moves']);
//      unset($_SESSION['user']);
    //}
    // Initialize the board
    if(!isset($_SESSION['board'])){
        $_SESSION['board'] = initializeBoard(5, 7);
        $_SESSION['moves'] = ceil(5 * 7 * 0.60);
    }
    // Check game over conditions
    if ($_SESSION['moves'] <= 0 || allShipsSunk($_SESSION['board'])) {
        $gameOver = true;
    } else {
        $gameOver = false;
    }
    echo "Moves left: " . $_SESSION['moves'];
    if ($gameOver) {
        echo '<p>';
        if(allShipsSunk($_SESSION['board'])){
            //echo 'You win!<br> <button><a href="?name=' . $_POST['name'] . '&reset=true">Play again</a></button>';
            displayFinalBoard($_SESSION['board']);
            echo 'You win!<br>';
            echo '<form action="battleship.php" method="post">';
            //echo '<input type="hidden" name="name" value="' . htmlspecialchars($_POST['name']) . '">';
            echo '<input type="hidden" name="reset" value="true">';
            echo '<button type="submit">Play again</button>';
            echo '</form>';
        }
        else{
            //echo 'You lose!<br> <button><a href="?name=' . $_POST['name'] . '&reset=true">Play again</a></button>';
            displayFinalBoard($_SESSION['board']);
            echo 'You lose!<br>';
            echo '<form action="battleship.php" method="post">';
            //echo '<input type="hidden" name="name" value="' . htmlspecialchars($_POST['name']) . '">';
            echo '<input type="hidden" name="reset" value="true">';
            echo '<button type="submit">Play again</button>';
            echo '</form>';
        }
        echo '<p>';
    }
    else {
        displayBoard($_SESSION['board']);
    }
}
else {
    // Display the form
    echo '<html>';
    echo '<head>';
    echo '<link rel="stylesheet" type="text/css" href="styles.css">';
    echo '</head>';
    echo '<body>';
    echo '<form action="battleship.php" method="post">';
    echo '<label for="name">Enter your name:</label>';
    echo '<input type="text" id="name" name="name" required>';
    echo '<input type="submit" value="Submit">';
    echo '</form>';
    echo '</body>';
    echo '</html>';
}
?>
