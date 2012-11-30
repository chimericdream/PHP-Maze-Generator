<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html;charset=utf-8" />
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
    </head>
    <body>
<?php
require_once './Maze.php';

$x = Maze::DEFAULT_MAZE_WIDTH;
$y = Maze::DEFAULT_MAZE_HEIGHT;

if (!empty($_GET['x']) && is_numeric($_GET['x'])) {
    $x = $_GET['x'];
}
if (!empty($_GET['y']) && is_numeric($_GET['y'])) {
    $y = $_GET['y'];
}
$m = new Maze((int) $x, (int) $y);
echo '<pre>';

$start = getTime();
$m->generate();
$finish = getTime();
$total_time = round(($finish - $start), 4);

$m->display();
echo '</pre>';
echo '<p>This ' . $x . 'x' . $y . ' maze was generated in ' . $total_time . ' seconds.</p>';
?>
    </body>
</html>
<?php
function getTime()
{
    $time = explode(' ', microtime());
    $time = $time[1] + $time[0];
    return $time;
} //end getTime