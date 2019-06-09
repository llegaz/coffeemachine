<?php

namespace LLegaz\CoffeeMachine\Tests;

use LLegaz\CoffeeMachine\CoffeeMachine as SUT;
use LLegaz\CoffeeMachine\Exception\CoffeeMachineException;

/**
 * This is not a 'real' Unit Tests suite here because we are not going
 * to mock anything but it is still really convenient to put everything
 * together when developing this project.
 *
 * The sole purpose of this class is to assert than the requirements for
 * CoffeeMachine class are fulfilled with expected behaviors.
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class CoffeeMachineTest extends \PHPUnit\Framework\TestCase
{
    private $sut;

    const EXPECTED_0_COIN = ['amount' => 0];
    const EXPECTED_1_COIN = ['amount' => 1];
    const EXPECTED_4_COINS = ['amount' => 4];
    const EXPECTED_10_COINS = ['amount' => 10];
    const EXPECTED_0_COINQ = [];
    const EXPECTED_1_COINQ = [0 => 'A shiny golden coin. Amazing!'];
    const EXPECTED_1_COINQ_ADV = [0 => 'A round piece made of copper.'];
    const EXPECTED_4_COINSQ = [
        0 => 'A shiny golden coin. Amazing!',
        1 => 'A shiny golden coin. Amazing!',
        2 => 'A round piece made of copper.',
        3 => 'A round piece made of copper.',
    ];
    const EXPECTED_0_STR = 'Credit: 0 coin';
    const EXPECTED_1_STR = 'Credit: 1 coin';
    const EXPECTED_2_STR = 'Credit: 2 coins';
    const EXPECTED_1337 = 'Credit: 1337 coins';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        // no dependency injection here, so we have nothing to mock :'(
        $this->sut = new SUT();
    }

    public function testInsertAndGetBackCurrentCoins()
    {
        $this->assertEquals(self::EXPECTED_0_COIN, $this->sut->getMoneyBackButton());
        $this->sut->addCoin();
        $this->assertEquals(self::EXPECTED_1_COIN, $this->sut->getMoneyBackButton());
        $this->sut->addCoin(4);
        $this->assertEquals(self::EXPECTED_4_COINS, $this->sut->getMoneyBackButton());
        try {
            $this->sut->addCoin(-1);
        } catch (CoffeeMachineException $cme) {
            // do nothing
        }
        $this->assertEquals(self::EXPECTED_0_COIN, $this->sut->getMoneyBackButton());
        for ($i = 5; $i; $i--) {
            $this->sut->addCoin(2);
        }
        $this->assertEquals(self::EXPECTED_10_COINS, $this->sut->getMoneyBackButton());
    }

    public function testInsertAndGetBackCurrentCoinsWithQueue()
    {
        $this->sut = new SUT(1, true);
        $this->assertEquals(self::EXPECTED_1_COINQ, $this->sut->getMoneyBackButton());
        $this->assertEquals(self::EXPECTED_0_COINQ, $this->sut->getMoneyBackButton());
        $this->sut = new SUT(2, true);
        $this->sut->addCoin(2);
        $this->assertEquals(self::EXPECTED_4_COINSQ, $this->sut->getMoneyBackButton());
    }

    public function testAssertDisplayWell()
    {
        $this->sut = new SUT(0, true);
        $this->assertStringContainsString(self::EXPECTED_0_STR, $this->sut->displayCurrentCoins());
        $this->sut->addCoin();
        $this->assertStringContainsString(self::EXPECTED_1_STR, $this->sut->displayCurrentCoins());
        $this->sut->addCoin();
        $this->assertStringContainsString(self::EXPECTED_2_STR, $this->sut->displayCurrentCoins());
        $this->sut = new SUT(1337);
        $this->assertStringContainsString(self::EXPECTED_1337, $this->sut->displayCurrentCoins());
    }

    public function testGetCoffee()
    {
        // regular flow testing
        $this->sut->addCoin(3);
        $drink = $this->sut->coffeeButton();
        $this->assertTrue($drink instanceof \LLegaz\CoffeeMachine\Drink\Coffee);
    }

    public function testGetTea()
    {
        // regular flow testing
        $this->sut->addCoin(3);
        $drink = $this->sut->teaButton();
        $this->assertTrue($drink instanceof \LLegaz\CoffeeMachine\Drink\Tea);
    }

    public function testGetChocolate()
    {
        // regular flow testing
        $this->sut->addCoin(5);
        $drink = $this->sut->chocolateButton();
        $this->assertTrue($drink instanceof \LLegaz\CoffeeMachine\Drink\Chocolate);
    }

    public function testSugar()
    {
        $this->sut->sugarButton(1);
        $this->sut->addCoin(3);
        $drink = $this->sut->teaButton();
        $this->assertEquals(1, $drink->getSugarLevel());
        //$this->assertTrue(!0);
    }

    public function testWithTooMuchSugar()
    {
        // silenced
        $this->sut = new SUT(45, false, true, 55, 55);
        $drink = $this->sut->teaButton();
        // verify we get the max autorized by the machine
        $this->assertEquals(4, $drink->getSugarLevel());
        // then reset and retry
        $this->sut->sugarButton(3);
        $drink = $this->sut->chocolateButton();
        $this->assertEquals(3, $drink->getSugarLevel());
    }

    /**
     * @expectedException LLegaz\CoffeeMachine\Exception\CoffeeMachineException
     */
    public function testSugarWithException()
    {
        $this->sut->sugarButton(5);
    }

    public function testMilk()
    {
        $this->sut->milkButton(3)->addCoin(2);
        $drink = $this->sut->coffeeButton();
        $this->assertEquals(3, $drink->getMilkLevel());
    }

    /**
     * @expectedException LLegaz\CoffeeMachine\Exception\CoffeeMachineException
     */
    public function testMilkWithException()
    {
        $this->sut->milkButton(5);
    }

    /**
     * @expectedException LLegaz\CoffeeMachine\Exception\CoffeeMachineException
     */
    public function testCoffeeMachineException()
    {
        $this->sut->addCoin(-1);
    }

    /**
     * And Let's dig a bit more just for the fun...
     */
    public function testAdvancedFlowWithErrors()
    {
        $this->sut = new SUT(6, true);
        $drink1 = $this->sut->coffeeButton();
        $drink2 = $this->sut->coffeeButton();
        $this->assertTrue(
            $drink1 instanceof \LLegaz\CoffeeMachine\Drink\Coffee
            && $drink2 instanceof \LLegaz\CoffeeMachine\Drink\Coffee
        );
        $this->expectExceptionObject(new CoffeeMachineException('You did not insert the right amount of coins, it is 5 for a Chocolate'));
        $this->sut->chocolateButton();
    }

    public function testAdvancedFlow()
    {
        $this->sut = new SUT(10, true);
        $drink1 = $this->sut->chocolateButton();
        $drink2 = $this->sut->teaButton();
        $this->assertTrue(
            $drink1 instanceof \LLegaz\CoffeeMachine\Drink\Chocolate
            && $drink2 instanceof \LLegaz\CoffeeMachine\Drink\Tea
        );
        $this->sut->addCoin(5);
        $drink1 = $this->sut->teaButton();
        $drink2 = $this->sut->teaButton();
        $this->assertTrue(
            $drink1 instanceof \LLegaz\CoffeeMachine\Drink\Tea
            && $drink2 instanceof \LLegaz\CoffeeMachine\Drink\Tea
        );
        $this->assertEquals(self::EXPECTED_1_COINQ_ADV, $this->sut->getMoneyBackButton());
    }

    public function testAdvancedFlowWithoutQueue()
    {
        $drink1 = $this->sut->addCoin(10)->chocolateButton();
        $drink2 = $this->sut->teaButton();
        $this->assertTrue(
            $drink1 instanceof \LLegaz\CoffeeMachine\Drink\Chocolate
            && $drink2 instanceof \LLegaz\CoffeeMachine\Drink\Tea
        );
        $this->sut->addCoin(5);
        $drink1 = $this->sut->teaButton();
        $drink2 = $this->sut->teaButton();
        $this->assertTrue(
            $drink1 instanceof \LLegaz\CoffeeMachine\Drink\Tea
            && $drink2 instanceof \LLegaz\CoffeeMachine\Drink\Tea
        );
        $this->assertEquals(self::EXPECTED_1_COIN, $this->sut->getMoneyBackButton());
    }
}
