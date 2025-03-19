<?php

declare(strict_types=1);
namespace Gate;

class Gate{
    public $position;


    public function __construct(array $position){
        $this->position = $position;
    }


}