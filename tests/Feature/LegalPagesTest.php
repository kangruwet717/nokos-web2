<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_legal_pages_are_publicly_accessible(): void
    {
        foreach ([
            '/terms' => 'Terms of Service',
            '/privacy' => 'Privacy Policy',
            '/refund-policy' => 'Refund Policy',
            '/acceptable-use' => 'Acceptable Use Policy',
            '/contact' => 'Contact and Abuse Report',
        ] as $path => $heading) {
            $this->get($path)
                ->assertOk()
                ->assertSee($heading);
        }
    }
}
