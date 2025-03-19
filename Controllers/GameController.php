<?php 
declare(strict_types=1);
namespace GameController;

require_once __DIR__ . '/../Model/Gate.php';
require_once __DIR__ . '/../Model/Shape.php';

use Shape\Shape;
use Gate\Gate;
class GameController{
    public $gate1;
    public $gate2;
    public $shapes;
    private $result;
    //home:
    // 0,689,1189,200
    // 726,689,1189,926

    public function __construct(){
        $this->gate1 = new Gate([0,689,1189,200]);
        $this->gate2 = new Gate([726,689,1189,926]);
        $this->shapes=[];
        $this->result=[];
        
    }

    private function createResult($id){
        $this->result[$id] = 0;
    }

    private function increaseResult($id){
        $this->result[$id]++;

        print_r($this->result);
    }


    private function removeResult($id){
        unset($this->result[$id]);
    }

    public function createShape($id){
        $shape = new Shape($id,$this->gate1,$this->gate2);
        array_push($this->shapes,$shape);

        $this->createResult($id);
        return $shape;
    }

    public function getShapes(){
        return $this->shapes;
    }

    public function removeShape($id){
        $filtereduserShapes = array_filter($this->shapes, function($shape) use ($id){
            return $shape->id !== $id;
        });

        $this->removeResult($id);
        $this->shapes = $filtereduserShapes;
    }


    public function checkPosition($position,$id) {
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
        // echo "shape";
        // print_r($position);
        // echo "gt1";
        // print_r($this->gate1->position);
        // echo "gt2";
        // print_r($this->gate2->position);
        $this->increaseResult($id);
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


    public function getResult(){
        return $this->result;
    }




}