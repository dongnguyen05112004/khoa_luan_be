<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HealthMetric;
use App\Models\Checkin;
use App\Models\AiRecommendation;
use App\Models\MemberSubscription;
use Carbon\Carbon;

class CustomerProgressController extends Controller
{
    /**
     * GET /api/customer/progress?period=monthly
     * Tổng hợp toàn bộ dữ liệu tiến trình cho trang "Theo dõi Tiến trình"
     *
     * period: weekly | monthly | quarterly
     */
    public function index(Request $request)
    {
        $user   = $request->user();
        $period = $request->get('period', 'monthly'); // weekly | monthly | quarterly

        // ── 1. Xác định khoảng thời gian ──────────────────────────
        $now = Carbon::now();
        switch ($period) {
            case 'weekly':
                $from = $now->copy()->subDays(7);
                $prevFrom = $now->copy()->subDays(14);
                $prevTo   = $now->copy()->subDays(7);
                break;
            case 'quarterly':
                $from = $now->copy()->subMonths(3);
                $prevFrom = $now->copy()->subMonths(6);
                $prevTo   = $now->copy()->subMonths(3);
                break;
            default: // monthly
                $from = $now->copy()->subDays(30);
                $prevFrom = $now->copy()->subDays(60);
                $prevTo   = $now->copy()->subDays(30);
                break;
        }

        // ── 2. Health Metrics (tất cả bản ghi để vẽ chart) ─────────
        $allMetrics = HealthMetric::where('user_id', $user->id)
            ->orderBy('record_date', 'asc')
            ->get(['id', 'record_date', 'weight', 'height', 'body_fat_percentage', 'muscle_mass_kg', 'bmi']);

        // Filter theo period để vẽ chart
        $periodMetrics = $allMetrics->filter(fn($m) =>
            Carbon::parse($m->record_date)->greaterThanOrEqualTo($from)
        )->values();

        // Nếu period metrics rỗng, lấy 10 bản ghi gần nhất
        $chartMetrics = $periodMetrics->count() > 0
            ? $periodMetrics
            : $allMetrics->take(-10)->values();

        // ── 3. Tính "Monthly Wins" (so sánh period hiện tại vs kỳ trước) ──
        $latestMetric   = $allMetrics->last();
        $previousMetric = $allMetrics->count() > 1 ? $allMetrics->slice(-2, 1)->first() : null;

        $fatDelta    = ($latestMetric && $previousMetric)
            ? round($latestMetric->body_fat_percentage - $previousMetric->body_fat_percentage, 2)
            : 0;
        $muscleDelta = ($latestMetric && $previousMetric)
            ? round($latestMetric->muscle_mass_kg - $previousMetric->muscle_mass_kg, 2)
            : 0;
        $weightDelta = ($latestMetric && $previousMetric)
            ? round($latestMetric->weight - $previousMetric->weight, 2)
            : 0;

        // ── 4. Checkin stats ────────────────────────────────────────
        $checkinsPeriod = Checkin::where('user_id', $user->id)
            ->where('check_in_at', '>=', $from)
            ->orderBy('check_in_at', 'asc')
            ->get(['id', 'check_in_at', 'check_out_at', 'duration']);

        $checkinCount      = $checkinsPeriod->count();
        $avgDurationMin    = $checkinsPeriod->avg('duration') ?? 0; // minutes
        $totalWorkoutHours = round($checkinsPeriod->sum('duration') / 60, 1);

        // Weekly breakdown cho chart check-in
        $checkinByWeek = $checkinsPeriod->groupBy(fn($c) =>
            Carbon::parse($c->check_in_at)->startOfWeek()->format('d/m')
        )->map->count()->toArray();

        // ── 5. BMR + Body Age tính từ latest metric ────────────────
        $bmr      = null;
        $bodyAge  = null;
        $bmi      = null;
        $bmiLabel = null;

        if ($latestMetric) {
            $bmi = $latestMetric->bmi;

            // Ước tính BMR theo Mifflin-St Jeor (dùng thể trọng & chiều cao)
            // Cần giới tính – lấy từ user
            $weightKg = $latestMetric->weight;
            $heightCm = $latestMetric->height;
            if ($weightKg && $heightCm) {
                $age = $user->memberProfile?->date_of_birth
                    ? Carbon::parse($user->memberProfile->date_of_birth)->age
                    : 25;
                if ($user->gender === 'male') {
                    $bmr = round(10 * $weightKg + 6.25 * $heightCm - 5 * $age + 5);
                } else {
                    $bmr = round(10 * $weightKg + 6.25 * $heightCm - 5 * $age - 161);
                }

                // Body Age Score: estimate based on BMI proximity to ideal (22.0)
                $bmiIdeal = 22.0;
                $bmiDiff  = abs($bmi - $bmiIdeal);
                $bodyAge  = $age + round($bmiDiff * 0.8);
            }

            // BMI label
            if ($bmi < 18.5)      $bmiLabel = 'THIẾU CÂN';
            elseif ($bmi < 25.0)  $bmiLabel = 'BÌNH THƯỜNG';
            elseif ($bmi < 30.0)  $bmiLabel = 'THỪA CÂN';
            else                   $bmiLabel = 'BÉO PHÌ';
        }

        // ── 6. AI Recommendation mới nhất ──────────────────────────
        $latestAi = AiRecommendation::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first(['id', 'title', 'ai_diagnosis', 'ai_suggestions', 'recommendation_type', 'created_at']);

        // ── 7. Gói tập hiện tại ─────────────────────────────────────
        $activeSub = MemberSubscription::with('plan')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->latest('start_date')
            ->first();

        $daysLeft = $activeSub
            ? max(0, Carbon::parse($activeSub->end_date)->diffInDays(Carbon::now(), false) * -1)
            : 0;

        // ── 8. Build chart data ─────────────────────────────────────
        $chartData = $chartMetrics->map(fn($m) => [
            'date'   => Carbon::parse($m->record_date)->format('d/m'),
            'weight' => (float) $m->weight,
            'fat'    => (float) $m->body_fat_percentage,
            'muscle' => (float) $m->muscle_mass_kg,
            'bmi'    => (float) $m->bmi,
        ])->values();

        // ── 9. Return ───────────────────────────────────────────────
        return response()->json([
            'message' => 'Lấy dữ liệu tiến trình thành công',
            'data' => [
                // Chart
                'chart_data'   => $chartData,
                'period'       => $period,

                // Latest snapshot
                'latest_metric' => $latestMetric ? [
                    'weight'              => $latestMetric->weight,
                    'height'              => $latestMetric->height,
                    'body_fat_percentage' => $latestMetric->body_fat_percentage,
                    'muscle_mass_kg'      => $latestMetric->muscle_mass_kg,
                    'bmi'                 => $latestMetric->bmi,
                    'bmi_label'           => $bmiLabel,
                    'record_date'         => $latestMetric->record_date,
                ] : null,

                // Deltas (Monthly Wins)
                'deltas' => [
                    'fat_delta'    => $fatDelta,
                    'muscle_delta' => $muscleDelta,
                    'weight_delta' => $weightDelta,
                ],

                // Derived stats
                'stats' => [
                    'bmr'                => $bmr,
                    'body_age'           => $bodyAge,
                    'bmi'                => $bmi,
                    'bmi_label'          => $bmiLabel,
                    'checkin_count'      => $checkinCount,
                    'avg_duration_min'   => round($avgDurationMin),
                    'total_workout_hrs'  => $totalWorkoutHours,
                    'days_left'          => $daysLeft,
                    'plan_name'          => $activeSub?->plan?->name ?? 'Chưa có gói',
                ],

                // AI
                'ai_recommendation' => $latestAi,

                // Checkin heatmap
                'checkin_by_week' => $checkinByWeek,
            ]
        ]);
    }
}
