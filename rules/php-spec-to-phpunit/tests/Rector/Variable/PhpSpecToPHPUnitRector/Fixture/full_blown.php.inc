<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use Ecommerce\Cart\Cart;
use Ecommerce\Cart\Exception\NotFoundException;
use Ecommerce\Component\Factory\EntityFactory;
use Ecommerce\Pricing\Currency\DefaultCurrency;
use Ecommerce\Pricing\Currency\DummyDefaultCurrencyProvider;
use Ecommerce\Pricing\Price\CalculatedPrice;
use Ecommerce\Pricing\Price\Price;
use Ecommerce\Taxing\PriceType;
use Ramsey\Uuid\Uuid;
use PhpSpec\ObjectBehavior;

class CartSpec extends ObjectBehavior
{
    public function let()
    {
        DefaultCurrency::setProvider(new DummyDefaultCurrencyProvider());

        $this->beConstructedWith(Uuid::uuid1());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Cart::class);
    }

    public function it_should_add_new_item()
    {
        $id       = Uuid::uuid1();
        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->add(new \Ecommerce\Cart\Item($id, 'Lorem ipsum', 4, new Price(671, $taxRate, $currency)));

        $item = $this->item($id);

        $item->id()->shouldReturn($id);
        $item->quantity()->shouldReturn(4);
    }

    public function it_should_provide_item_by_its_identifier()
    {
        $id       = Uuid::uuid1();
        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->add(new \Ecommerce\Cart\Item($id, 'Lorem ipsum', 3, new Price(671, $taxRate, $currency)));

        $this->item($id)->shouldBeAnInstanceOf(\Ecommerce\Cart\Contract\Item::class);
    }

    public function it_should_throw_an_exception_when_item_is_not_in_cart()
    {
        $id = Uuid::uuid1();

        $this->shouldThrow(NotFoundException::class)->during('item', [$id]);
    }

    public function it_should_summ_up_quantities_when_adding_same_item_multiple_times()
    {
        PriceType::set(PriceType::WITH_VAT);

        $id       = Uuid::uuid1();
        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->add(new \Ecommerce\Cart\Item($id, 'Lorem ipsum', 3, new Price(671, $taxRate, $currency)));
        $this->add(new \Ecommerce\Cart\Item($id, 'Dolor sit amet', 7, new Price(671, $taxRate, $currency)));

        $item = $this->item($id);

        $item->quantity()->shouldReturn(10);
    }

    public function it_should_remove_item()
    {
        PriceType::set(PriceType::WITH_VAT);

        $id       = Uuid::uuid1();
        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->add(new \Ecommerce\Cart\Item($id, 'Lorem ipsum', 3, new Price(671, $taxRate, $currency)));
        $this->remove($id);

        $this->shouldThrow(NotFoundException::class)->during('item', [$id]);
    }

    public function it_should_calculate_zero_price_when_cart_is_empty()
    {
        $price = $this->price();

        $price->shouldBeAnInstanceOf(CalculatedPrice::class);
        $price->withVat()->shouldReturn(0.0);
        $price->withoutVat()->shouldReturn(0.0);
        $price->vat()->shouldReturn(0.0);
    }

    public function it_should_calculate_total_price_of_all_items_when_not_empty()
    {
        PriceType::set(PriceType::WITH_VAT);

        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->add(new \Ecommerce\Cart\Item(Uuid::uuid1(), 'Lorem ipsum', 3, new Price(671, $taxRate, $currency)));

        $price = $this->price();

        $price->shouldReturnAnInstanceOf(CalculatedPrice::class);

        $price->withVat()->shouldReturn(2013.00);
        $price->withoutVat()->shouldReturn(1663.54);
        $price->vat()->shouldReturn(349.46);
    }

    public function it_should_contain_old_items_when_merged_with_empty_cart()
    {
        PriceType::set(PriceType::WITH_VAT);

        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $cart = new Cart(Uuid::uuid1());

        $item1 = new \Ecommerce\Cart\Item(Uuid::fromString('3cf9f49c-af76-11e8-8981-529269fb1459'), 'Lorem ipsum', 2, new Price(671, $taxRate, $currency));
        $item2 = new \Ecommerce\Cart\Item(Uuid::fromString('3cf9f80c-af76-11e8-8981-529269fb1459'), 'Dolor sit amet', 3, new Price(475, $taxRate, $currency));

        $this->add($item1);
        $this->add($item2);

        $this->merge($cart);

        $items = $this->items();
        $items->shouldHaveCount(2);

        $this->item($item1->key())->quantity()->shouldBe(2);
        $this->item($item2->key())->quantity()->shouldBe(3);
    }

    public function it_should_contain_only_items_from_second_cart_when_merged_with_not_empty_cart()
    {
        PriceType::set(PriceType::WITH_VAT);

        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $cart = new Cart(Uuid::uuid1());

        $item1 = new \Ecommerce\Cart\Item(Uuid::fromString('3cf9f49c-af76-11e8-8981-529269fb1459'), 'Lorem ipsum', 2, new Price(671, $taxRate, $currency));
        $item2 = new \Ecommerce\Cart\Item(Uuid::fromString('3cf9f80c-af76-11e8-8981-529269fb1459'), 'Dolor sit amet', 3, new Price(475, $taxRate, $currency));
        $item3 = new \Ecommerce\Cart\Item(Uuid::fromString('3cf9f49c-af76-11e8-8981-529269fb1459'), 'Lorem ipsum', 4, new Price(671, $taxRate, $currency));
        $item4 = new \Ecommerce\Cart\Item(Uuid::fromString('3cf9fa0a-af76-11e8-8981-529269fb1459'), 'Lorem ipsum', 5, new Price(671, $taxRate, $currency));

        $cart->add($item1);
        $cart->add($item2);

        $this->add($item3);
        $this->add($item4);

        $this->merge($cart);

        $items = $this->items();
        $items->shouldHaveCount(2);

        $this->item($item1->key())->quantity()->shouldBe(2);
        $this->item($item2->key())->quantity()->shouldBe(3);
    }
}

?>
-----
<?php

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use Ecommerce\Cart\Cart;
use Ecommerce\Cart\Exception\NotFoundException;
use Ecommerce\Component\Factory\EntityFactory;
use Ecommerce\Pricing\Currency\DefaultCurrency;
use Ecommerce\Pricing\Currency\DummyDefaultCurrencyProvider;
use Ecommerce\Pricing\Price\CalculatedPrice;
use Ecommerce\Pricing\Price\Price;
use Ecommerce\Taxing\PriceType;
use Ramsey\Uuid\Uuid;
use PhpSpec\ObjectBehavior;

class CartTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Cart $cart;
    protected function setUp()
    {
        DefaultCurrency::setProvider(new DummyDefaultCurrencyProvider());

        $this->cart = new \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Cart(Uuid::uuid1());
    }

    public function testInitializable()
    {
        $this->assertInstanceOf(Cart::class, $this->cart);
    }

    public function testAddNewItem()
    {
        $id       = Uuid::uuid1();
        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->cart->add(new \Ecommerce\Cart\Item($id, 'Lorem ipsum', 4, new Price(671, $taxRate, $currency)));

        $item = $this->cart->item($id);

        $this->assertSame($id, $item->id());
        $this->assertSame(4, $item->quantity());
    }

    public function testProvideItemByItsIdentifier()
    {
        $id       = Uuid::uuid1();
        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->cart->add(new \Ecommerce\Cart\Item($id, 'Lorem ipsum', 3, new Price(671, $taxRate, $currency)));

        $this->assertInstanceOf(\Ecommerce\Cart\Contract\Item::class, $this->cart->item($id));
    }

    public function testThrowAnExceptionWhenItemIsNotInCart()
    {
        $id = Uuid::uuid1();

        $this->expectException(NotFoundException::class);
        $this->cart->item($id);
    }

    public function testSummUpQuantitiesWhenAddingSameItemMultipleTimes()
    {
        PriceType::set(PriceType::WITH_VAT);

        $id       = Uuid::uuid1();
        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->cart->add(new \Ecommerce\Cart\Item($id, 'Lorem ipsum', 3, new Price(671, $taxRate, $currency)));
        $this->cart->add(new \Ecommerce\Cart\Item($id, 'Dolor sit amet', 7, new Price(671, $taxRate, $currency)));

        $item = $this->cart->item($id);

        $this->assertSame(10, $item->quantity());
    }

    public function testRemoveItem()
    {
        PriceType::set(PriceType::WITH_VAT);

        $id       = Uuid::uuid1();
        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->cart->add(new \Ecommerce\Cart\Item($id, 'Lorem ipsum', 3, new Price(671, $taxRate, $currency)));
        $this->cart->remove($id);

        $this->expectException(NotFoundException::class);
        $this->cart->item($id);
    }

    public function testCalculateZeroPriceWhenCartIsEmpty()
    {
        $price = $this->cart->price();

        $this->assertInstanceOf(CalculatedPrice::class, $price);
        $this->assertSame(0.0, $price->withVat());
        $this->assertSame(0.0, $price->withoutVat());
        $this->assertSame(0.0, $price->vat());
    }

    public function testCalculateTotalPriceOfAllItemsWhenNotEmpty()
    {
        PriceType::set(PriceType::WITH_VAT);

        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $this->cart->add(new \Ecommerce\Cart\Item(Uuid::uuid1(), 'Lorem ipsum', 3, new Price(671, $taxRate, $currency)));

        $price = $this->cart->price();

        $this->assertInstanceOf(CalculatedPrice::class, $price);

        $this->assertSame(2013.00, $price->withVat());
        $this->assertSame(1663.54, $price->withoutVat());
        $this->assertSame(349.46, $price->vat());
    }

    public function testContainOldItemsWhenMergedWithEmptyCart()
    {
        PriceType::set(PriceType::WITH_VAT);

        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $cart = new Cart(Uuid::uuid1());

        $item1 = new \Ecommerce\Cart\Item(Uuid::fromString('3cf9f49c-af76-11e8-8981-529269fb1459'), 'Lorem ipsum', 2, new Price(671, $taxRate, $currency));
        $item2 = new \Ecommerce\Cart\Item(Uuid::fromString('3cf9f80c-af76-11e8-8981-529269fb1459'), 'Dolor sit amet', 3, new Price(475, $taxRate, $currency));

        $this->cart->add($item1);
        $this->cart->add($item2);

        $this->cart->merge($cart);

        $items = $this->cart->items();
        $this->assertCount(2, $items);

        $this->assertSame(2, $this->cart->item($item1->key())->quantity());
        $this->assertSame(3, $this->cart->item($item2->key())->quantity());
    }

    public function testContainOnlyItemsFromSecondCartWhenMergedWithNotEmptyCart()
    {
        PriceType::set(PriceType::WITH_VAT);

        $taxRate  = EntityFactory::make('tax_rate_21');
        $currency = EntityFactory::make('currency_czk');

        $cart = new Cart(Uuid::uuid1());

        $item1 = new \Ecommerce\Cart\Item(Uuid::fromString('3cf9f49c-af76-11e8-8981-529269fb1459'), 'Lorem ipsum', 2, new Price(671, $taxRate, $currency));
        $item2 = new \Ecommerce\Cart\Item(Uuid::fromString('3cf9f80c-af76-11e8-8981-529269fb1459'), 'Dolor sit amet', 3, new Price(475, $taxRate, $currency));
        $item3 = new \Ecommerce\Cart\Item(Uuid::fromString('3cf9f49c-af76-11e8-8981-529269fb1459'), 'Lorem ipsum', 4, new Price(671, $taxRate, $currency));
        $item4 = new \Ecommerce\Cart\Item(Uuid::fromString('3cf9fa0a-af76-11e8-8981-529269fb1459'), 'Lorem ipsum', 5, new Price(671, $taxRate, $currency));

        $cart->add($item1);
        $cart->add($item2);

        $this->cart->add($item3);
        $this->cart->add($item4);

        $this->cart->merge($cart);

        $items = $this->cart->items();
        $this->assertCount(2, $items);

        $this->assertSame(2, $this->cart->item($item1->key())->quantity());
        $this->assertSame(3, $this->cart->item($item2->key())->quantity());
    }
}

?>
