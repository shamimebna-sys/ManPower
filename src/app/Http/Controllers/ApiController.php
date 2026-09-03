<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    /**
     * Inactivate candidates who haven't improved to final group within 1 month.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inactivateCandidates(Request $request)
    {
        // Simple security token check
        $token = $request->header('X-Cron-Token') ?: $request->query('token');
        $expectedToken = env('CRON_TOKEN', 'default_cron_token_123');

        if ($token !== $expectedToken) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $oneMonthAgo = date('Y-m-d H:i:s', strtotime('-1 month'));

        $query = Candidate::withoutGlobalScopes()
            ->whereIn('status', ['A', 'P'])
            ->where('created_at', '<', $oneMonthAgo)
            ->where(function($q) {
                $q->whereNull('class_group_id')
                    ->orWhereDoesntHave('classGroup', function($subQ) {
                        $subQ->where('name', 'like', '%final%');
                    });
            });

        $inactivatedCount = 0;

        try {
            DB::transaction(function() use ($query, &$inactivatedCount) {
                $query->chunkById(200, function($candidates) use (&$inactivatedCount) {
                    $candidateIds = $candidates->pluck('id')->toArray();

                    if (!empty($candidateIds)) {
                        // Update candidates status to inactive 'I'
                        Candidate::withoutGlobalScopes()->whereIn('id', $candidateIds)->update([
                            'status' => 'I',
                            'updated_at' => now(),
                            'remarks'=>'Inactive candidates for not improving to final group within 1 month'
                        ]);

                        // Update linked users status to inactive 'I'
                        User::whereIn('candidate_id', $candidateIds)->update([
                            'status' => 'I',
                            'updated_at' => now()
                        ]);

                        $inactivatedCount += count($candidateIds);
                    }
                });
            });

            Log::info("Cronjob successfully executed. Inactivated {$inactivatedCount} candidates.");

            return response()->json([
                'status' => true,
                'message' => 'Candidates processed successfully',
                'inactivated_count' => $inactivatedCount
            ], 200);

        } catch (\Exception $e) {
            Log::error("Failed to run candidates inactivation cronjob: " . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to process candidates',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
