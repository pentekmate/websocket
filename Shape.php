<?php
declare(strict_types=1);

namespace Shape;
class Shape{
    public $type;
    public $position;
    public $id;


    private $gate1Position = [0,229,729,200];
    private $gate2Position = [752,229,729,952];
    private $shapes = ["triangle","circle","square"];

    public function __construct($id)
    {
        $this->id = $id;
        $this->type = $this->generateRandomShape();
        $this->position = $this->generatePositon();
    }

    private function generatePositon():array{
        $top = rand($this->gate1Position[0],$this->gate2Position[0]);
        $left =  rand($this->gate1Position[1],$this->gate2Position[1]);
        $right = rand($this->gate1Position[2],$this->gate2Position[2]);
        $bottom = rand($this->gate1Position[3],$this->gate2Position[3]);

        return [$top,$left,$right,$bottom];
    }

    private function generateRandomShape():string{
        return $this->shapes[rand(0, count($this->shapes) - 1)];
    }
}