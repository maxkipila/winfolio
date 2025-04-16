<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserAward;

class UserAwardTest extends TestCase
{
    /*     use RefreshDatabase; */

    /** @test */
    public function test_can_claim_a_badge()
    {
        // Vytvoříme odznak, který ještě nebyl claimnut
        $award = UserAward::factory()->create([
            'claimed_at' => null,
            'user_id' => null,
        ]);

        // Vytvoříme uživatele, který bude odznak claimovat
        $user = User::factory()->create();

        // Provedeme claim
        $award->claim($user);

        // Assertiony
        $award->refresh();
        $this->assertNotNull($award->claimed_at);
        $this->assertEquals($user->id, $award->user_id);
    }

    /** @test */
    public function test_cannot_claim_already_claimed_badge()
    {
        // Vytvoříme odznak, který je již claimnutý
        $award = UserAward::factory()->create([
            'claimed_at' => now(),
        ]);

        $user = \App\Models\User::factory()->create();

        $this->expectException(\Exception::class);

        $award->claim($user);
    }
}
