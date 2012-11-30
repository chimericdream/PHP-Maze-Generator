<?php
/**
 * Maze.php
 *
 * This file contains the declaration for the Maze class.
 *
 * @file       Maze.php
 * @author     Bill Parrott <bparrott@ku.edu>
 * @date       11/30/2012
 * @version    1.0.0
 */

class Maze
{
    const DEFAULT_MAZE_HEIGHT = 15;
    const DEFAULT_MAZE_WIDTH  = 15;

    private $cells            = array();
    private $walls            = array();
    private $eqClasses        = array();
    private $remainingClasses = 0;
    private $x                = 0;
    private $y                = 0;
    private $cellCount        = 0;
    private $wallCount        = 0;


    /**
     * __construct()
     *
     * Class constructor. This sets the height and width of the maze then calls
     * buildBaseMaze() to initialize the arrays.
     *
     * @param int x The width of the maze to generate; defaults to DEFAULT_MAZE_WIDTH
     * @param int y The height of the maze to generate; defaults to DEFAULT_MAZE_HEIGHT
     * @return n/a (Constructor)
     */
    public function __construct($x = self::DEFAULT_MAZE_WIDTH, $y = self::DEFAULT_MAZE_HEIGHT)
    {
        $this->x = $x;
        $this->y = $y;

        $this->buildBaseMaze();
    } //end __construct
    

    /**
     * generate()
     *
     * Magic! This method uses the union/find algorithm to determine whether
     * two adjacent rooms are in the same set. If they are not, it performs
     * a union by knocking out the wall between them. Once all cells in the
     * maze are in the same set, the maze is complete and there exists a
     * path from every cell in the maze to every other cell.
     *
     * @return void
     */
    public function generate()
    {
        while ($this->disjointCellsExist()) {
            $c    = $this->getRandomCell();
            $wall = $this->getRandomInnerWall($c);
            $n    = $this->getNeighboringCell($c, $wall);

            if (!$this->checkConnected($c, $n)) {
                $this->removeWall($wall);
                $this->connect($c, $n);
            }
        }
    } //end generate


    /**
     * display()
     *
     * Wrapper for the other print function that defaults to using std::cout
     * as the output location.
     *
     * @return void
     */
    public function display()
    {
        // Each row is 2 character lines high, including top border, then add 1 for
        // bottom border of the maze
        $printRows = (2 * $this->y) + 1;

        $currWall = 0;
        $currCell = 0;
        for ($i = 0; $i < $printRows; $i++) {
            if ($i % 2 == 0) {
                // Printing a top border
                for ($j = 0; $j < $this->x; $j++) {
                    // Top-left corner does not need top-left divider; needs a space
                    // to line everything up, though.
                    if ($j > 0 || $i > 0) {
                        echo "&#x2591;";
                    } else {
                        echo " ";
                    }
                    if ($this->walls[$currWall++] == 1) {
                        echo "&#x2591;&#x2591;";
                    } else {
                        echo "  ";
                    }
                }
                // Bottom-right corner does not need bottom-right divider
                if ($i != ($printRows - 1)) {
                    echo "&#x2591;";
                }
            } else {
                // Printing the cell itself
                for ($j = 0; $j < $this->x; $j++) {
                    if ($this->walls[$currWall++] == 1) {
                        echo "&#x2591;";
                    } else {
                        echo " ";
                    }
                    echo "  ";
                }

                // Print the right wall if needed
                if ($this->walls[$currWall++] == 1) {
                    echo "&#x2591;";
                }
            }
            echo "<br />";
        }
    } //end display


    /**
     * buildBaseMaze()
     *
     * Initializes the grid of cells/rooms in the maze. To start, all cells
     * are in their own equivalence class. Also initializes all walls as
     * visible. Finally, knock out the walls in the upper-left and
     * bottom-right corners.
     *
     * @return void
     */
    private function buildBaseMaze()
    {
        $this->cellCount        = $this->x * $this->y;
        $this->wallCount        = ($this->x * ($this->y + 1)) + ($this->y * ($this->x + 1));
        $this->remainingClasses = $this->cellCount;

        // Set all the walls to be on to start
        for ($i = 0; $i < $this->wallCount; $i++) {
            $this->walls[$i] = 1;
        }

        // Initialize the cells to be independent of one another
        for ($i = 0; $i < $this->cellCount; $i++) {
            $c = array(
                'idx'    => $i,
                'parent' => NULL,
            );
            $this->cells[$i]     = $c;
            $this->eqClasses[$i] = $i;
        }

        // Remove top-left corner walls
        $this->removeWall(0);
        $this->removeWall($this->x);

        // Remove bottom-right corner walls
        $this->removeWall($this->wallCount - ($this->x + 1));
        $this->removeWall($this->wallCount - 1);
    } //end buildBaseMaze


    /**
     * checkConnected()
     *
     * The find portion of the union/find algorithm, this checks whether the
     * specified cells have the same equivalence class (i.e. whether they)
     * are connected within the maze.
     *
     * @return bool Whether two cells have the same equivalence class.
     */
    private function checkConnected(array $c1, array $c2)
    {
        if ($this->eqClasses[$c1['idx']] == $this->eqClasses[$c2['idx']]) {
            return true;
        } else {
            return false;
        }
    } //end checkConnected


    /**
     * connect()
     *
     * Using the union portion of the union/find algorithm, this method
     * connects two cells/rooms in the maze. It gets the equivalence class
     * of c1 and sets c2's parent to c1's root parent. It then sets the
     * equivalence class of all cells that matched c2's old class to equal
     * c1's equivalence class.
     *
     * @return void
     */
    private function connect(array $c1, array $c2)
    {
        // The root parent of c1 is its equivalence class.
        $temp1 = $this->cells[$this->eqClasses[$c1['idx']]];
        $temp1['parent'] = $c2['idx'];

        $oldClass  = $this->eqClasses[$temp1['idx']];
        $newClass  = $this->eqClasses[$c2['idx']];

        for ($i = 0; $i < $this->cellCount; $i++) {
            if ($this->eqClasses[$i] == $oldClass) {
                $this->eqClasses[$i] = $newClass;
            }
        }

        $this->remainingClasses--;
    } //end connect


    /**
     * removeWall()
     *
     * Set the specified wall to invisible.
     *
     * @return void
     */
    private function removeWall($idx)
    {
        $this->walls[$idx] = 0;
    } //end removeWall


    /**
     * getRandomCell()
     *
     * Pick a cell at random within the maze. This is the first step during
     * the generation process.
     *
     * @return Cell* A random cell in the maze.
     */
    private function getRandomCell()
    {
        $idx = rand(0, $this->cellCount - 1);
        return $this->cells[$idx];
    } //end getRandomCell


    /**
     * getRandomInnerWall()
     *
     * Given a particular cell/room in the maze, pick one of its inner walls
     * at random. An inner wall is one that is adjacent to another cell/room
     * in the maze and not part of the outer border of the overall grid.
     *
     * @return int The index of a random inner wall adjacent to the room.
     */
    private function getRandomInnerWall(array $c)
    {
        $n = $s = $e = $w = true;
        if ($c['idx'] < $this->x) {
            $n = false;
        }
        if ($c['idx'] >= ($this->cellCount - $this->x)) {
            $s = false;
        }
        if ($c['idx'] % $this->x == ($this->x - 1)) {
            $e = false;
        }
        if ($c['idx'] % $this->x == 0) {
            $w = false;
        }

        // Determine which row the cell is in
        $row = floor($c['idx'] / $this->x);

        // Wall indexes for the north, west, east, and south
        $nx = $c['idx'] + ($row * $this->x) + $row;
        $wx = $nx + $this->x;
        $ex = $nx + $this->x + 1;
        $sx = $nx + (2 * $this->x) + 1;

        while (true) {
            $wall = rand(1,4);
            switch ($wall) {
                case 1:
                    if ($n === true) {
                        return $nx;
                    }
                    break;
                case 2:
                    if ($w === true) {
                        return $wx;
                    }
                    break;
                case 3:
                    if ($e === true) {
                        return $ex;
                    }
                    break;
                case 4:
                    if ($s === true) {
                        return $sx;
                    }
                    break;
            }
        }
    } //end getRandomInnerWall


    /**
     * getNeighboringCell()
     *
     * Given a cell and a wall, this method determines which room is on the
     * other side of said wall.
     *
     * @return Cell* Returns a pointer to the cell on the other side of the
     *               wall.
     */
    private function getNeighboringCell(array $c, $wall)
    {
        // Determine which row the cell is in
        $row = floor($c['idx'] / $this->x);

        // Wall indexes for the north, west, east, and south
        $n = $c['idx'] + ($row * $this->x) + $row;
        $w = $n + $this->x;
        $e = $n + $this->x + 1;
        $s = $n + (2 * $this->x) + 1;

        // Indexes for the cells to the north, west, east, and south
        $nx = $c['idx'] - $this->x;
        $wx = $c['idx'] - 1;
        $ex = $c['idx'] + 1;
        $sx = $c['idx'] + $this->x;

        if ($wall == $n) {
            return $this->cells[$nx];
        }
        if ($wall == $w) {
            return $this->cells[$wx];
        }
        if ($wall == $e) {
            return $this->cells[$ex];
        }
        if ($wall == $s) {
            return $this->cells[$sx];
        }
    } //end getNeighboringCell


    /**
     * disjointCellsExist()
     *
     * This method checks how many equivalence classes remain. The number of
     * equivalence classes represents how many disjoint sets of cells there
     * are. Initially it is equal to the number of cells in the maze. Each
     * time a wall is knocked out, two sets become connected, so the total
     * number of disjoint sets is reduced by one. Once the number of sets
     * reaches 1, it means that all cells in the maze are connected to all
     * other cells by at least one path.
     *
     * @return bool Whether any disjoint cells still exist
     */
    private function disjointCellsExist()
    {
        if ($this->remainingClasses > 1) {
            return true;
        }

        return false;
    } //end disjointCellsExist
} //end class Maze