<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Expense;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    // === EMPLOYEES / USERS ===

    public function getEmployees()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();
        
        return response()->json([
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return response()->json(['message' => 'Employee created successfully', 'user' => $user->load('roles')]);
    }

    // === EXPENSES ===

    public function getExpenses()
    {
        $expenses = Expense::with('user')->orderBy('date', 'desc')->get();
        return response()->json(['data' => $expenses]);
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $expense = Expense::create([
            'uuid' => Str::uuid(),
            'branch_id' => 1, // hardcoded for MVP
            'user_id' => $request->user()->id ?? 1, // fallback
            'category' => $request->category,
            'amount' => $request->amount,
            'date' => $request->date,
            'notes' => $request->notes
        ]);

        return response()->json(['message' => 'Expense recorded successfully', 'data' => $expense]);
    }
}
