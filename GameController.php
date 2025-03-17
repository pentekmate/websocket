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
    
    //home:
    // 0,689,1189,200
    // 726,689,1189,926

    public function __construct(){
        $this->gate1 = new Gate([0,689,1189,200]);
        $this->gate2 = new Gate([726,689,1189,926]);
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


    public function checkPosition($position) {
        $gate1Condition = 
            $position[3] >= $this->gate1->position[0] && 
            $position[0] <= $this->gate1->position[3] &&             
            $position[2] >= $this->gate1->position[1] && 
            $position[1] <= $this->gate1->position[2];     

        $gate2Condition = 
            $position[3] >= $this->gate2->position[0] &&
            $position[0] <= $this->gate2->position[3] &&
            $position[2] >= $this->gate2->position[1] &&
            $position[1] <= $this->gate2->position[2];
    
       if($gate1Condition || $gate2Condition){
        echo "shape";
        print_r($position);
        echo "gt1";
        print_r($this->gate1->position);
        echo "gt2";
        print_r($this->gate2->position);
        return true;

       }
    }
    

    public function moveShape($id, $position){
        foreach ($this->shapes as $key => $shape) {
            if ($shape->id === $id) {
               
                $shape->position = $position; 
                break;
            }
        }
    }



    public function restoreShapePosition(){
        foreach($this->shapes as $shape){
            $shape->resetPosition();
        }
    }




}