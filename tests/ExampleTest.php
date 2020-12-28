<?php

namespace Flightsadmin\LivewireCrud\Tests;

use Orchestra\Testbench\TestCase;
use Flightsadmin\LivewireCrud\LivewireCrudServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LivewireCrudServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
