<?php

namespace App\Http\Controllers;

use App\Models\InputAspirasi;
use App\Models\Aspirasi;
use App\Models\Siswa;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    /**
     * Get dashboard statistics with date filtering.
     * Optimized: uses single query with conditional counting.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $period = $request->get('period', 'all');
        
        // Single query with conditional counts via subquery
        $query = DB::table('input_aspirasi as ia');
        $this->applyPeriodFilterRaw($query, $period, 'ia.created_at');
        
        $total = (clone $query)->count();
        
        // Use efficient EXISTS subquery for status counts
        $completed = (clone $query)->whereExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('aspirasi')
                ->whereColumn('aspirasi.id_pelaporan', 'ia.id_pelaporan')
                ->where('aspirasi.status', 'Selesai');
        })->count();
        
        $inProgress = (clone $query)->whereExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('aspirasi')
                ->whereColumn('aspirasi.id_pelaporan', 'ia.id_pelaporan')
                ->where('aspirasi.status', 'Proses');
        })->count();
        
        $pending = $total - $completed - $inProgress;
        
        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'pending' => $pending,
                'in_progress' => $inProgress,
                'completed' => $completed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ]
        ]);
    }

    /**
     * Get trend data for charts — pure SQL grouping (no PHP ->get() on all records).
     * Optimized: uses DB-level GROUP BY instead of loading all records into memory.
     */
    public function trend(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        
        // Determine date range and SQL group expression
        switch ($period) {
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                $groupExpr = 'DATE(ia.created_at)';
                $format = 'D';
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $groupExpr = 'DATE(ia.created_at)';
                $format = 'd M';
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                $groupExpr = "DATE_FORMAT(ia.created_at, '%Y-%m')";
                $format = 'M';
                break;
            default:
                $startDate = Carbon::now()->subMonths(6);
                $endDate = Carbon::now();
                $groupExpr = "DATE_FORMAT(ia.created_at, '%Y-%m')";
                $format = 'M';
        }

        // Single efficient query: GROUP BY date with LEFT JOIN for completed count
        $dbResults = DB::table('input_aspirasi as ia')
            ->select(
                DB::raw("{$groupExpr} as date_key"),
                DB::raw('COUNT(*) as pengaduan'),
                DB::raw('COUNT(DISTINCT CASE WHEN a.status = "Selesai" THEN ia.id_pelaporan END) as selesai')
            )
            ->leftJoin('aspirasi as a', 'a.id_pelaporan', '=', 'ia.id_pelaporan')
            ->whereBetween('ia.created_at', [$startDate, $endDate])
            ->groupBy('date_key')
            ->orderBy('date_key')
            ->get()
            ->keyBy('date_key');

        // Build complete timeline (fill gaps with zeros)
        $trendData = [];
        $current = $startDate->copy();
        $isYearly = in_array($period, ['year', 'default']);
        
        while ($current <= $endDate) {
            if ($isYearly) {
                $key = $current->format('Y-m');
                $label = $current->format($format);
                $next = $current->copy()->addMonth();
            } else {
                $key = $current->format('Y-m-d');
                $label = $current->format($format);
                $next = $current->copy()->addDay();
            }

            $row = $dbResults->get($key);
            $trendData[] = [
                'name' => $label,
                'date' => $key,
                'pengaduan' => $row ? (int)$row->pengaduan : 0,
                'selesai' => $row ? (int)$row->selesai : 0,
            ];

            $current = $next;
            
            if ($period === 'week' && count($trendData) >= 7) break;
            if ($period === 'month' && count($trendData) >= 31) break;
            if ($period === 'year' && count($trendData) >= 12) break;
        }

        return response()->json([
            'success' => true,
            'data' => $trendData,
            'period' => $period,
            'range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ]
        ]);
    }

    /**
     * Get category distribution statistics.
     * Optimized: single JOIN query instead of separate queries.
     */
    public function categoryDistribution(Request $request): JsonResponse
    {
        $period = $request->get('period', 'all');
        
        $query = DB::table('input_aspirasi as ia')
            ->join('kategori as k', 'k.id_kategori', '=', 'ia.id_kategori')
            ->select('k.ket_kategori as name', DB::raw('COUNT(*) as value'));
        
        $this->applyPeriodFilterRaw($query, $period, 'ia.created_at');
        
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#84cc16'];
        
        $data = $query->groupBy('k.id_kategori', 'k.ket_kategori')
            ->orderByDesc('value')
            ->get()
            ->map(function ($item, $index) use ($colors) {
                return [
                    'name' => $item->name,
                    'value' => (int)$item->value,
                    'color' => $colors[$index % count($colors)],
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get status distribution statistics.
     * Optimized: uses efficient EXISTS subquery.
     */
    public function statusDistribution(Request $request): JsonResponse
    {
        $period = $request->get('period', 'all');
        
        $query = DB::table('input_aspirasi as ia');
        $this->applyPeriodFilterRaw($query, $period, 'ia.created_at');
        
        $total = (clone $query)->count();
        
        $completed = (clone $query)->whereExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('aspirasi')
                ->whereColumn('aspirasi.id_pelaporan', 'ia.id_pelaporan')
                ->where('aspirasi.status', 'Selesai');
        })->count();
        
        $inProgress = (clone $query)->whereExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('aspirasi')
                ->whereColumn('aspirasi.id_pelaporan', 'ia.id_pelaporan')
                ->where('aspirasi.status', 'Proses');
        })->count();
        
        $pending = $total - $completed - $inProgress;

        return response()->json([
            'success' => true,
            'data' => [
                ['name' => 'Menunggu', 'value' => $pending, 'color' => '#f59e0b'],
                ['name' => 'Proses', 'value' => $inProgress, 'color' => '#3b82f6'],
                ['name' => 'Selesai', 'value' => $completed, 'color' => '#10b981'],
            ]
        ]);
    }

    /**
     * Get comprehensive statistics summary.
     * Optimized: all counts in minimal queries.
     */
    public function summary(Request $request): JsonResponse
    {
        $period = $request->get('period', 'all');
        
        $query = DB::table('input_aspirasi as ia');
        $this->applyPeriodFilterRaw($query, $period, 'ia.created_at');
        
        $total = (clone $query)->count();
        $uniqueReporters = (clone $query)->distinct()->count('nis');
        
        $completed = (clone $query)->whereExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('aspirasi')
                ->whereColumn('aspirasi.id_pelaporan', 'ia.id_pelaporan')
                ->where('aspirasi.status', 'Selesai');
        })->count();
        
        // Most common category — single query
        $topCategory = (clone $query)
            ->join('kategori as k', 'k.id_kategori', '=', 'ia.id_kategori')
            ->select('k.ket_kategori')
            ->groupBy('ia.id_kategori', 'k.ket_kategori')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(1)
            ->value('k.ket_kategori');

        return response()->json([
            'success' => true,
            'data' => [
                'total_pengaduan' => $total,
                'total_siswa' => DB::table('siswa')->count(),
                'siswa_aktif_pelapor' => $uniqueReporters,
                'tingkat_selesai' => $total > 0 ? round(($completed / $total) * 100) : 0,
                'kategori_terbanyak' => $topCategory ?? '-',
                'rata_rata_per_hari' => $period === 'month' ? round($total / max(Carbon::now()->day, 1), 1) : null,
            ]
        ]);
    }

    /**
     * Helper: apply period filter to raw DB query builder.
     */
    private function applyPeriodFilterRaw($query, string $period, string $column = 'created_at'): void
    {
        switch ($period) {
            case 'week':
                $query->where($column, '>=', Carbon::now()->startOfWeek());
                break;
            case 'month':
                $query->where($column, '>=', Carbon::now()->startOfMonth());
                break;
            case 'year':
                $query->where($column, '>=', Carbon::now()->startOfYear());
                break;
        }
    }
}
