<?php

namespace Tests\Unit;

use App\Models\PricingRule;
use App\Models\Site;
use App\Models\Box;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingRuleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test pricing rule creation
     */
    public function test_can_create_pricing_rule()
    {
        $site = Site::factory()->create();

        $rule = PricingRule::create([
            'site_id' => $site->id,
            'name' => 'Test Rule',
            'occupancy_threshold_min' => 70,
            'occupancy_threshold_max' => 85,
            'adjustment_type' => 'percentage',
            'adjustment_value' => 10,
            'priority' => 100,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(PricingRule::class, $rule);
        $this->assertEquals('Test Rule', $rule->name);
        $this->assertEquals(10, $rule->adjustment_value);
    }

    /**
     * Test active scope
     */
    public function test_active_scope_filters_correctly()
    {
        $activeRule = PricingRule::factory()->create([
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ]);

        $inactiveRule = PricingRule::factory()->create([
            'is_active' => false,
        ]);

        $expiredRule = PricingRule::factory()->create([
            'is_active' => true,
            'valid_until' => now()->subDay(),
        ]);

        $activeRules = PricingRule::active()->get();

        $this->assertTrue($activeRules->contains($activeRule));
        $this->assertFalse($activeRules->contains($inactiveRule));
        $this->assertFalse($activeRules->contains($expiredRule));
    }

    /**
     * Test apply to price with percentage
     */
    public function test_apply_to_price_with_percentage()
    {
        $rule = PricingRule::factory()->create([
            'adjustment_type' => 'percentage',
            'adjustment_value' => 10, // +10%
        ]);

        $basePrice = 100;
        $newPrice = $rule->applyToPrice($basePrice);

        $this->assertEquals(110, $newPrice);
    }

    /**
     * Test apply to price with fixed amount
     */
    public function test_apply_to_price_with_fixed_amount()
    {
        $rule = PricingRule::factory()->create([
            'adjustment_type' => 'fixed',
            'adjustment_value' => 15, // +15€
        ]);

        $basePrice = 100;
        $newPrice = $rule->applyToPrice($basePrice);

        $this->assertEquals(115, $newPrice);
    }

    /**
     * Test season detection
     */
    public function test_current_season_detection()
    {
        $januaryDate = Carbon::create(2025, 1, 15);
        $this->travelTo($januaryDate);

        $rule = new PricingRule();
        $reflection = new \ReflectionClass($rule);
        $method = $reflection->getMethod('getCurrentSeason');
        $method->setAccessible(true);

        $season = $method->invoke($rule);
        $this->assertEquals('winter', $season);

        $this->travelBack();
    }

    /**
     * Test isCurrentlyValid method
     */
    public function test_is_currently_valid()
    {
        $validRule = PricingRule::factory()->create([
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ]);

        $this->assertTrue($validRule->isCurrentlyValid());

        $expiredRule = PricingRule::factory()->create([
            'is_active' => true,
            'valid_from' => now()->subMonth(),
            'valid_until' => now()->subDay(),
        ]);

        $this->assertFalse($expiredRule->isCurrentlyValid());
    }
}
