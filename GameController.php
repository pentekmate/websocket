<?php 
declare(strict_types=1);
namespace GameController;

require_once __DIR__ . '/Gate.php';
require_once __DIR__ . '/Shape.php';

use Shape\Shape;
use Gate\Gate;
class GameController{
    public $gate1;
    public $gate2;
    public $shapes;
    


    public function __construct(){
        $this->gate1 = new Gate([0, 229, 729, 200]);
        $this->gate2 = new Gate([752, 229, 729, 952]);
        $this->shapes=[];
    }

    public function createShape($id){
        $shape = new Shape($id,$this->gate1,$this->gate2);
        array_push($this->shapes,$shape);

        return $shape;
    }

    public function getShapes(){
        return $this->shapes;
    }

    public function removeShape($id){
        $filtereduserShapes = array_filter($this->shapes, function($shape) use ($id){
            return $shape->id !== $id;
        });

       $this->shapes = $filtereduserShapes;
    }


    public function checkPosition($position){
        // Példa logika: ellenőrizzük, hogy a pozíció elérte-e valamelyik kaput
        $gate1Condition = $position[0] < $this->gate1->position[2] && $position[1] > $this->gate1->position[1];
        $gate2Condition = $position[0] < $this->gate2->position[2] && $position[1] > $this->gate2->position[1];
        
       $gyasz = $positon[0];
       echo $gyasz;

        return $gate1Condition || $gate2Condition;
    }

    public function moveShape($id, $position){
        foreach ($this->shapes as $key => $shape) {
            if ($shape->id === $id) {
                // Helyesen frissítjük az adott elem pozícióját
                $shape->position = $position; // A helyes mód
                break;
            }
        }
    }




}