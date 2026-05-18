<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserListResource;
use App\Http\Resources\UserCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /** GET /api/users */
    public function index(Request $request)
    {
        $query = User::with(['role', 'branch'])
            ->when($request->role_id, fn($q) => $q->where('role_id', $request->role_id))
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->state, fn($q) => $q->where('state', $request->state))
            ->when($request->search, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('name', 'like', '%' . $request->search . '%')
                   ->orWhere('email', 'like', '%' . $request->search . '%')
                   ->orWhere('phone', 'like', '%' . $request->search . '%');
            }))
            ->orderBy('id');

        $users = $this->attachDisplayIds($this->paginateUsers($query, $request, 20));

        return new UserCollection($users);
    }

    /** POST /api/users */
    public function store(Request $request)
    {
        // ===== PHÂN QUYỀN THEO ROLE NGƯỜI GỌI =====
        $caller     = $request->user()->load('role');
        $callerRole = strtolower($caller->role?->role_name ?? '');

        // Lấy role_name của role sẽ được gán cho user mới
        $targetRoleId   = $request->input('role_id');
        $targetRoleName = null;

        if ($targetRoleId) {
            $targetRole     = \App\Models\Role::find($targetRoleId);
            $targetRoleName = $targetRole ? strtolower($targetRole->role_name) : null;
        }

        // Hệ thống chỉ có 1 Admin, không ai được phép tạo thêm (kể cả admin hiện tại)
        if ($targetRoleName === 'admin') {
            return response()->json([
                'message' => 'Hệ thống chỉ cho phép 1 tài khoản Admin duy nhất. Không thể tạo thêm.',
            ], 403);
        }

        // --- Admin: được tạo mọi role (trừ admin đã chặn ở trên) ---
        if ($callerRole === 'admin') {
            // Không giới hạn
        }
        // --- Manager: không được tạo admin ---
        elseif ($callerRole === 'manager') {
            if ($targetRoleName === 'admin') {
                return response()->json([
                    'message' => 'Bạn không có quyền tạo tài khoản Admin.',
                ], 403);
            }
        }
        // --- Staff (Lễ tân): chỉ được tạo member ---
        elseif ($callerRole === 'staff') {
            if ($targetRoleName && $targetRoleName !== 'member') {
                return response()->json([
                    'message' => 'Lễ tân chỉ được phép tạo tài khoản Hội viên.',
                ], 403);
            }
            // Nếu không truyền role_id, tự động gán member
            if (!$targetRoleId) {
                $memberRole = \App\Models\Role::where('role_name', 'member')->first();
                if ($memberRole) {
                    $request->merge(['role_id' => $memberRole->id]);
                }
            }
        }
        // --- Các role khác: không có quyền tạo tài khoản nội bộ ---
        else {
            return response()->json([
                'message' => 'Bạn không có quyền tạo tài khoản.',
            ], 403);
        }

        // ===== VALIDATE & TẠO USER =====
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|string|min:8',
            'role_id'     => 'required|exists:roles,id',
            'branch_id'   => 'nullable|exists:branches,id',
            'full_name'   => 'nullable|string|max:150',
            'phone'       => 'nullable|string|max:20',
            'gender'      => 'nullable|in:male,female,other',
            'avatar'      => 'nullable|string|max:255',
            'card_number' => 'nullable|string|max:50',
            'e_number'    => 'nullable|string|max:50',
            'state'       => 'nullable|in:active,inactive,banned',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        return new UserResource($user->load(['role', 'branch']));
    }

    /** GET /api/users/{id} */
    public function show($id)
    {
        $user = User::with([
            'role', 'branch', 'memberProfile', 'employeeProfile',
            'trainer', 'activeSubscription.plan',
        ])->findOrFail($id);
        return new UserResource($user);
    }

    /** PUT /api/users/{id} */
    public function update(Request $request, $id)
    {
        $user = User::with(['role', 'memberProfile', 'employeeProfile'])->findOrFail($id);
        
        $data = $request->validate([
            // User fields
            'name'        => 'sometimes|string|max:255',
            'email'       => 'sometimes|email|unique:users,email,' . $id,
            'password'    => 'sometimes|string|min:8',
            'role_id'     => 'nullable|exists:roles,id',
            'branch_id'   => 'nullable|exists:branches,id',
            'full_name'   => 'nullable|string|max:150',
            'phone'       => 'nullable|string|max:20',
            'gender'      => 'nullable|in:male,female,other',
            'avatar'      => 'nullable|string|max:255',
            'card_number' => 'nullable|string|max:50',
            'e_number'    => 'nullable|string|max:50',
            'state'       => 'nullable|in:active,inactive,banned',

            // Profile fields (Member)
            'date_of_birth'     => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:100',
            'health_notes'      => 'nullable|string',
            
            // Profile fields (Employee)
            'position'  => 'nullable|string|max:100',
            'salary'    => 'nullable|numeric',
            'hire_date' => 'nullable|date',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Update User
        $user->update($request->only([
            'name', 'email', 'password', 'role_id', 'branch_id', 
            'full_name', 'phone', 'gender', 'avatar', 
            'card_number', 'e_number', 'state'
        ]));

        // Update linked Profile if role is member
        $roleName = strtolower($user->role?->role_name ?? '');
        
        if ($roleName === 'member') {
            $profileData = $request->only(['date_of_birth', 'emergency_contact', 'health_notes']);
            if (!empty($profileData)) {
                $user->memberProfile()->updateOrCreate(['user_id' => $user->id], $profileData);
            }
        } 
        // Update linked Profile if role is staff/manager/trainer
        elseif (in_array($roleName, ['manager', 'staff', 'trainer', 'receptionist'])) {
            $empData = $request->only(['position', 'salary', 'hire_date']);
            if (!empty($empData)) {
                $user->employeeProfile()->updateOrCreate(['user_id' => $user->id], $empData);
            }
        }

        return new UserResource($user->load(['role', 'branch', 'memberProfile', 'employeeProfile']));
    }

    /** DELETE /api/users/{id} */
    public function destroy($id)
    {
        $userToDelete = User::withTrashed()->with('role')->findOrFail($id);

        if (strtolower($userToDelete->role?->role_name) === 'admin') {
            return response()->json(['message' => 'Không thể xóa tài khoản Admin hệ thống.'], 403);
        }

        DB::transaction(function () use ($userToDelete) {
            $userToDelete->tokens()->delete();
            $userToDelete->forceDelete();
        });

        return response()->json(['message' => 'Đã xóa vĩnh viễn tài khoản']);
    }

    /** GET /api/users/{id}/subscriptions */
    public function subscriptions($id)
    {
        $user = User::findOrFail($id);
        $subscriptions = $user->subscriptions()->with('plan', 'promotion')->get();
        return response()->json($subscriptions);
    }

    /** GET /api/users/{id}/checkins */
    public function checkins($id)
    {
        $user = User::findOrFail($id);
        $checkins = $user->checkins()->with('branch')->latest()->get();
        return response()->json($checkins);
    }

    /** GET /api/users/{id}/health-metrics */
    public function healthMetrics($id)
    {
        $user = User::findOrFail($id);
        $healthMetrics = $user->healthMetrics()->orderBy('record_date', 'desc')->get();
        return response()->json($healthMetrics);
    }
    /** GET /api/users/{id}/admin-get-user */
    public function adminGetUser(Request $request)
    {
        // 1. Khởi tạo Query
        $query = User::query()->with(['role', 'branch'])
            ->when($request->role_id, fn($q) => $q->where('role_id', $request->role_id))
            ->when($request->state, fn($q) => $q->where('state', $request->state))
            ->when($request->search, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('name', 'like', '%' . $request->search . '%')
                   ->orWhere('full_name', 'like', '%' . $request->search . '%')
                   ->orWhere('email', 'like', '%' . $request->search . '%')
                   ->orWhere('phone', 'like', '%' . $request->search . '%');
            }))
            ->orderBy('id');
        //Lọc theo chi nhánh
        $query->when($request->branch_id, function ($q, $branchId) {
            return $q->where('branch_id', $branchId);
        });
        //phân trang
        // Mặc định endpoint admin trả toàn bộ users để FE có đủ dữ liệu.
        // Truyền paginated=true nếu cần server-side pagination.
        $users = $this->attachDisplayIds($this->paginateUsers($query, $request, 15, true));

        return UserListResource::collection($users);
    }

    private function attachDisplayIds($users)
    {
        $start = $users->firstItem() ?? 1;

        $users->getCollection()->transform(function ($user, int $index) use ($start) {
            $user->display_id = $start + $index;
            return $user;
        });

        return $users;
    }

    private function paginateUsers($query, Request $request, int $defaultPerPage, bool $defaultAll = false)
    {
        $returnAll = !$request->boolean('paginated') && (
            $defaultAll
            || $request->boolean('all')
            || strtolower((string) $request->input('per_page')) === 'all'
        );

        if ($returnAll) {
            $total = (clone $query)->count();
            return $query->paginate(max($total, 1), ['*'], 'page', 1);
        }

        $perPage = (int) $request->input('per_page', $defaultPerPage);
        $perPage = max(1, min($perPage, 500));

        return $query->paginate($perPage);
    }
}
