<?php

namespace LLegaz\CoffeeMachine\Drink;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class Chocolate extends AbstractDrink
{
    use DrinkName;

    public function __construct()
    {
        $this->drinkName = 'Chocolate';
    }
}
