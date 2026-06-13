<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Exports\TrackingDateRangeExport;
use App\Services\Exports\TrackingSelectionExport;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ExportsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_away(): void
    {
        $this->get(route('export.tracking', ['type' => 'daterange']))
            ->assertRedirect();

        $this->assertGuest();
    }

    public function test_it_downloads_a_date_range_export(): void
    {
        Excel::fake();
        Carbon::setTestNow($now = Carbon::now());

        $filename = 'tracking-'.$now->format('y-m-d').'.xlsx';

        $response = $this->actingAs(User::factory()->create())
            ->get(route('export.tracking', [
                'type' => 'daterange',
                'start' => '2025-01-01',
                'end' => '2025-01-31',
            ]));

        $response->assertOk();

        Excel::assertDownloaded(
            $filename,
            fn (TrackingDateRangeExport $export) => true,
        );
    }

    public function test_it_downloads_a_selection_export(): void
    {
        Excel::fake();
        Carbon::setTestNow($now = Carbon::now());

        $filename = 'tracking-'.$now->format('y-m-d').'.xlsx';

        $response = $this->actingAs(User::factory()->create())
            ->get(route('export.tracking', [
                'type' => 'selection',
                'selection' => [1, 2, 3],
            ]));

        $response->assertOk();

        Excel::assertDownloaded(
            $filename,
            fn (TrackingSelectionExport $export) => true,
        );
    }

    public function test_it_returns_an_empty_response_for_an_unknown_type(): void
    {
        Excel::fake();

        $response = $this->actingAs(User::factory()->create())
            ->get(route('export.tracking', ['type' => 'something-else']));

        // No matching branch, so the controller falls through to an implicit
        // null return, which Laravel renders as an empty 200. This documents
        // the current behaviour rather than endorsing it.
        $response->assertOk();
        $this->assertSame('', $response->getContent());
    }
}