<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();

        $query = User::query()
            ->where('role', 'member')
            ->withCount(['orders' => fn ($q) => $q->where('status', 'SELESAI')])
            ->latest();

        if ($search !== '') {
            $pattern = '%' . mb_strtolower($search) . '%';
            $query->where(function ($q) use ($pattern) {
                $q->whereRaw('LOWER(COALESCE(member_code, \'\')) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(name) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(COALESCE(phone, \'\')) LIKE ?', [$pattern]);
            });
        }

        $members = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => User::where('role', 'member')->count(),
            'with_orders' => User::where('role', 'member')
                ->whereHas('orders', fn ($q) => $q->where('status', 'SELESAI'))
                ->count(),
            'this_month' => User::where('role', 'member')
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
        ];

        return view('admin.members.index', compact('members', 'stats', 'search'));
    }
}
