<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;
use Carbon\Carbon;

class DateHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Include helper file
        require_once app_path('Helpers/DateHelper.php');
    }

    /** @test */
    public function format_date_ymd_with_carbon_instance()
    {
        $date = Carbon::create(2023, 12, 25, 10, 30, 0);
        
        $result = formatDateYMD($date);
        
        $this->assertEquals('2023/12/25', $result);
    }

    /** @test */
    public function format_date_ymd_with_string_date()
    {
        $dateString = '2023-12-25 10:30:00';
        
        $result = formatDateYMD($dateString);
        
        $this->assertEquals('2023/12/25', $result);
    }

    /** @test */
    public function format_date_ymd_with_timestamp()
    {
        $timestamp = 1703505000; // 2023-12-25 10:30:00 UTC
        
        $result = formatDateYMD($timestamp);
        
        $this->assertEquals('2023/12/25', $result);
    }

    /** @test */
    public function format_date_ymd_with_null_returns_na()
    {
        $result = formatDateYMD(null);
        
        $this->assertEquals('N/A', $result);
    }

    /** @test */
    public function format_date_ymd_with_empty_string_returns_na()
    {
        $result = formatDateYMD('');
        
        $this->assertEquals('N/A', $result);
    }

    /** @test */
    public function format_date_dmy_with_carbon_instance()
    {
        $date = Carbon::create(2023, 12, 25, 10, 30, 0);
        
        $result = formatDateDMY($date);
        
        $this->assertEquals('25/12/2023', $result);
    }

    /** @test */
    public function format_date_dmy_with_string_date()
    {
        $dateString = '2023-12-25 10:30:00';
        
        $result = formatDateDMY($dateString);
        
        $this->assertEquals('25/12/2023', $result);
    }

    /** @test */
    public function format_date_dmy_with_timestamp()
    {
        $timestamp = 1703505000; // 2023-12-25 10:30:00 UTC
        
        $result = formatDateDMY($timestamp);
        
        $this->assertEquals('25/12/2023', $result);
    }

    /** @test */
    public function format_date_dmy_with_null_returns_na()
    {
        $result = formatDateDMY(null);
        
        $this->assertEquals('N/A', $result);
    }

    /** @test */
    public function format_date_ymdhis_with_carbon_instance()
    {
        $date = Carbon::create(2023, 12, 25, 10, 30, 45);
        
        $result = formatDateYMDHIS($date);
        
        $this->assertEquals('2023/12/25 10:30:45', $result);
    }

    /** @test */
    public function format_date_ymdhis_with_string_date()
    {
        $dateString = '2023-12-25 10:30:45';
        
        $result = formatDateYMDHIS($dateString);
        
        $this->assertEquals('2023/12/25 10:30:45', $result);
    }

    /** @test */
    public function format_date_ymdhis_with_timestamp()
    {
        $timestamp = 1703505045; // 2023-12-25 10:30:45 UTC
        
        $result = formatDateYMDHIS($timestamp);
        
        $this->assertEquals('2023/12/25 10:30:45', $result);
    }

    /** @test */
    public function format_date_ymdhis_with_null_returns_na()
    {
        $result = formatDateYMDHIS(null);
        
        $this->assertEquals('N/A', $result);
    }

    /** @test */
    public function format_date_dmyhis_with_carbon_instance()
    {
        $date = Carbon::create(2023, 12, 25, 10, 30, 45);
        
        $result = formatDateDMYHIS($date);
        
        $this->assertEquals('25/12/2023 10:30:45', $result);
    }

    /** @test */
    public function format_date_dmyhis_with_string_date()
    {
        $dateString = '2023-12-25 10:30:45';
        
        $result = formatDateDMYHIS($dateString);
        
        $this->assertEquals('25/12/2023 10:30:45', $result);
    }

    /** @test */
    public function format_date_dmyhis_with_timestamp()
    {
        $timestamp = 1703505045; // 2023-12-25 10:30:45 UTC
        
        $result = formatDateDMYHIS($timestamp);
        
        $this->assertEquals('25/12/2023 10:30:45', $result);
    }

    /** @test */
    public function format_date_dmyhis_with_null_returns_na()
    {
        $result = formatDateDMYHIS(null);
        
        $this->assertEquals('N/A', $result);
    }

    /** @test */
    public function all_helper_functions_exist()
    {
        $this->assertTrue(function_exists('formatDateYMD'));
        $this->assertTrue(function_exists('formatDateDMY'));
        $this->assertTrue(function_exists('formatDateYMDHIS'));
        $this->assertTrue(function_exists('formatDateDMYHIS'));
    }
}
