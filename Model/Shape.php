<?php

declare(strict_types=1);

namespace Shape;

require_once __DIR__ . '/Gate.php';

use Gate\Gate;

class Shape {
    public $type;
    public $position;
    public $id;
    private $shapeSize = 100;

    private $gate1Position;
    private $gate2Position;

    private $shapes = ["circle", "square"];

    public function __construct($id,$gate1Position,$gate2Position) {
        $this->gate1Position = $gate1Position;
        $this->gate2Position = $gate2Position;
        $this->id = $id;
        $this->type = $this->generateRandomShape();
        $this->position = $this->generatePositon();
    }

    private function generatePositon(): array {
        $buffer = 20;
        $left = rand($this->gate1Position->position[1], $this->gate2Position->position[1]);
        $right = rand($this->gate1Position->position[2], $this->gate2Position->position[2]);
       
        $top = rand($this->gate1Position->position[3] + $this->shapeSize + $buffer, 
        $this->gate2Position->position[0] - $this->shapeSize - $buffer);

        $bottom = rand($this->gate1Position->position[3] + $this->shapeSize + $buffer, 
           $this->gate2Position->position[0] - $this->shapeSize - $buffer);

        return [$top, $left, $right, $bottom];
    }

    private function generateRandomShape(): string {
        return $this->shapes[rand(0, count($this->shapes) - 1)];
    }

    public function getPosition() {
        return $this->position;
    }


    public function resetPosition(){
        return $this->position = $this->generatePositon();
    }

}
