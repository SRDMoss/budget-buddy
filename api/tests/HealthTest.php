<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HealthTest extends TestCase
{
    public function testEnvLoaded(): void
    {
        // APP_ENV should exist (or default in your code)
        $this->assertNotEmpty($_ENV['APP_ENV'] ?? null, 'APP_ENV was not loaded');
    }
}
