<?php declare(strict_types=1);

namespace LLegaz\CoffeeMachine;

use LLegaz\CoffeeMachine\Drink\{
    AbstractDrink,
    Chocolate,
    Coffee,
    Tea,
};
use LLegaz\CoffeeMachine\Exception\CoffeeMachineException;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class CoffeeMachine
{
    private $coins;
    private $isQueued = false;
    private $isSilencedException = false;
    private $sugarLvl;
    private $milkLvl;

    // the coffee machine will be responsible for sugar/milk levels
    const MAXIMUM_LEVEL = 4;
    const MINIMUM_LEVEL = 0;

    // Prices table for all products by coins amount
    const PRICES = [
        Coffee::class => 2,
        Tea::class => 3,
        Chocolate::class => 5,
    ];
    
    public function __construct(int $coins = 0, bool $isSplQueue = false, bool $isSilencedException = false, int $sugarLvl = 0, int $milkLvl = 0)
    {
        // keep track of the number of coins only
        if (!$isSplQueue) {
            $this->coins = $coins;
        } else {
            // or modelize it with a FIFO queue of differrent coins
            $this->coins = new \SplQueue();
            $this->isQueued = true;
            for ($i = $coins; $i; $i--) {
                $this->coins[] = new class {
                    public function __toString()
                    {
                        return 'A shiny golden coin. Amazing!';
                    }
                };
            }
        }
        if ($isSilencedException) {
            $this->isSilencedException = true;
        }
        $this->setValue('sugar', $sugarLvl);
        $this->setValue('milk', $milkLvl);
    }

    /**
     *
     * @param int $amount
     * @return void
     * @throws CoffeeMachineException
     */
    final public function addCoin(int $amount = 1) : self
    {
        if ($amount <= 0) {
            throw new CoffeeMachineException('You should put some coins first.');
        }

        if ($this->isQueued) {
            for ($i = $amount; $i; $i--) {
                $this->coins[] = new class {
                    public function __toString()
                    {
                        return 'A round piece made of copper.';
                    }
                };
            }
        } else {
            $this->coins +=$amount;
        }

        return $this;
    }
    
    private function retrieveCoin(int $amount) : array
    {
        $toReturn = [];
        $this->coins->setIteratorMode(\SplQueue::IT_MODE_DELETE);
        while ($amount--) {
            $toReturn[] = sprintf('%s', $this->coins->dequeue());
        }

        return $toReturn;
    }

    private function checkCoinAmount(int $amount) : bool
    {
        if ($this->isQueued) {
            return $this->coins->count() >= $amount;
        }

        return $this->coins >= $amount;
    }

    /**
     *
     * @param int $amount  -  expected subtracted coins amount
     * @return int  - the number of accounted coins
     */
    private function subtractFromCoinAmount(int $amount) : int
    {
        if ($this->isQueued) {
            return count($this->retrieveCoin($amount));
        }
        $this->coins -= $amount;
        
        return $amount;
    }

    private function prepareAskedDrink(AbstractDrink $toPrepare) : AbstractDrink
    {
        if ($this->checkCoinAmount(self::PRICES[get_class($toPrepare)])) {
            $this->subtractFromCoinAmount(self::PRICES[get_class($toPrepare)]);
            $toPrepare->addSugar($this->sugarLvl);
            $toPrepare->addMilk($this->milkLvl);
        } else {
            throw new CoffeeMachineException(
                'You did not insert the right amount of coins, it is ' . self::PRICES[get_class($toPrepare)] . ' for a ' . $toPrepare->getDrinkName()
            );
        }

        return $toPrepare;
    }

    final public function coffeeButton() : Coffee
    {
        return $this->prepareAskedDrink(new Coffee());
    }

    final public function teaButton() : Tea
    {
        return $this->prepareAskedDrink(new Tea());
    }

    final public function chocolateButton() : Chocolate
    {
        return $this->prepareAskedDrink(new Chocolate());
    }
    
    /**
     * 0 - no milk
     * 1 - 1 drop
     * 2 - 2 drops
     * 3 - 3 drops
     * 4 - 4 drops
     *
     * @param int $value
     * @return void
     * @throws CoffeeMachineException
     */
    final public function milkButton(int $value = 1) : self
    {
        $this->setValue('milk', $value);

        return $this;
    }

    /**
     * 0 - no sugar
     * 1 - 1 spoon
     * 2 - 2 spoons
     * 3 - 3 spoons
     * 4 - 4 spoons
     *
     * @param int $value
     * @return void
     * @throws CoffeeMachineException
     */
    final public function sugarButton(int $value = 1) : self
    {
        $this->setValue('sugar', $value);

        return $this;
    }
    
    /**
     *
     * @param type $varname
     * @param type $value
     * @return void
     * @throws CoffeeMachineException
     */
    private function setValue($varname, $value) : void
    {
        if ($this->isSilencedException) {
            if ($value < self::MINIMUM_LEVEL) {
                $this->{$varname . 'Lvl'} = self::MINIMUM_LEVEL;
            } elseif ($value > self::MAXIMUM_LEVEL) {
                $this->{$varname . 'Lvl'} = self::MAXIMUM_LEVEL;
            } else {
                $this->{$varname . 'Lvl'} = $value;
            }
        } else {
            if ($value < self::MINIMUM_LEVEL || $value > self::MAXIMUM_LEVEL) {
                throw new CoffeeMachineException('Wrong value for ' . $varname . '.');
            } else {
                $this->{$varname . 'Lvl'} = $value;
            }
        }
    }

    final public function getMoneyBackButton() : array
    {
        if ($this->isQueued) {
            return $this->retrieveCoin($this->coins->count());
        } else {
            $toReturn = $this->coins;
            $this->coins = 0;

            return ['amount' => $toReturn];
        }
    }

    final public function displayCurrentCoins() : string
    {
        $str = 'Credit: ';
        $bln = false;
        if ($this->isQueued) {
            $str .= $this->coins->count();
            $bln = $this->coins->count() > 1;
        } else {
            $str .= $this->coins;
            $bln = $this->coins > 1;
        }

        return $str . ' coin' . ($bln?'s':'');
    }
}
