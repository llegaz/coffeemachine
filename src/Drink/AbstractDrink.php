<?php declare(strict_types=1);

namespace LLegaz\CoffeeMachine\Drink;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
abstract class AbstractDrink implements DrinkableInterface
{
    private $milk = 0;
    private $sugar = 0;
    
    abstract public function getDrinkName();

    final public function addMilk(int $milk=1) : int
    {
        /*if ($milk < 0) {
            throw new \LogicException('it could be a complicated operation to withdraw milk from that ' . $this->getDrinkName()); //but why not
        }*/
        
        if ($this->milk+$milk < 0) {
            throw new \LogicException($this->customErrorMsg());
        }

        $this->milk += $milk;

        return $this->milk;
    }

    final public function addSugar(int $sugar=1) : int
    {
        /*if ($sugar < 0) {
            // same interrogation here..
        }*/

        if ($this->sugar+$sugar < 0) {
            throw new \LogicException($this->customErrorMsg());
        }

        $this->sugar += $sugar;

        return $this->sugar;
    }

    private function customErrorMsg() : string
    {
        return 'it is not permitted by chemical laws. The ' . $this->getDrinkName() . ' stay unchanged.';
    }

    final public function getMilkLevel()
    {
        return $this->milk;
    }
    
    final public function getSugarLevel()
    {
        return $this->sugar;
    }

    final public function displayDrinkComposition()
    {
        $statement = $this->sugar?' with some sugar (value = ' . $this->sugar . ')':'';
        if ($statement!=='') {
            $statement .= ($this->milk?' and some milk (value = ' . $this->milk . ')':'');
        } else {
            $statement = $this->milk?' with some milk (value = ' . $this->milk . ')':'';
        }
        
        $statement .= '.';
        
        return 'A hot ' . $this->getDrinkName() . $statement;
    }
}
