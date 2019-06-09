<?php

namespace LLegaz\CoffeeMachine\Drink;

trait DrinkName
{
    private $drinkName;

    public function getDrinkName() : string
    {
        return $this->drinkName;
    }
}
