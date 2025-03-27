<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\_User;
use App\Models\User;
use App\Traits\HasUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    use HasUtils;
    /*  public function index()
    {
        return view('admin.dashboard');
    }
 */
    public function index(Request $request)
    {

        $start = $request->start ? Carbon::parse($request->start)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $request->end ? Carbon::parse($request->end)->endOfDay() : Carbon::now()->endOfMonth();

        $data = NULL;

        if (($request->start || $request->end) || !$data) {
            [$users, $transactions, $sales, $best, $transactionsByDay, $transactionsByDayPrevious, $p_start, $p_end, $start, $end,] = $this->calculate($request);
            $data = compact('users', 'p_start', 'p_end');
        } else {

            $users = $data->users;
        }
        $top_users = User::orderBy('created_at', 'desc');
        $top_users_collection = fn() => _User::collection($top_users->paginate($request->paginate ?? 5));

        return Inertia::render('Admin/Dashboard', compact('start', 'end', 'data', 'users', 'top_users_collection'));
    }

    public function calculate($request)
    {
        $start = $request?->start ? Carbon::parse($request->start)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $request?->end ? Carbon::parse($request->end)->endOfDay() : Carbon::now()->endOfMonth();

        $diff = $start->diffInDays($end);

        $p_start = $start->copy()->subDays($diff + 1)->startOfDay();
        $p_end = $start->copy()->subDays(1)->endOfDay();

        $new_user_total = User::whereBetween('created_at', [$start, $end])->count();
        $new_user_before_total = User::whereBetween('created_at', [$p_start, $p_end])->count();

        $user_total = User::where('created_at', '<', $end)->count();
        $user_before_total = User::where('created_at', '<', $p_end)->count();

        $active = User::whereBetween('created_at', [$start, $end])->count();

        $active_before = User::whereBetween('created_at', [$p_start, $p_end])->count();

        $users = [
            'total' => $user_total,
            'before_total' => $user_before_total,
            'diff' => $user_total - $user_before_total,
            'percentage' => $this->percentChange($user_before_total, $user_total) * 100,

            'active_total' => $active,
            'active_before_total' => $active_before,
            'active_diff' => $active - $active_before,
            'active_percentage' => $this->percentChange($active_before, $active) * 100,

            'new_total' => $new_user_total,
            'new_before_total' => $new_user_before_total,
            'new_diff' => $new_user_total - $new_user_before_total,
            'new_percentage' => $this->percentChange($new_user_before_total, $new_user_total) * 100,
        ];

        return [
            $users,
            [], // transactions
            [], // sales
            [], // best
            [], // transactionsByDay
            [], // transactionsByDayPrevious 
            $p_start,
            $p_end,
            $start,
            $end,
            [], // taskers
            [], // joberts
            [], // categories
        ];
    }
    function percentChange($oldValue, $newValue)
    {
        // Check if the old value is zero to avoid division by zero
        if ($oldValue == 0) {
            // Handle the case where old value is zero
            return $this->sign($newValue) * 100;
        } else {
            // Calculate percentage change when old value is not zero
            return $this->floor((($newValue - $oldValue) / $oldValue));
        }
    }
}
