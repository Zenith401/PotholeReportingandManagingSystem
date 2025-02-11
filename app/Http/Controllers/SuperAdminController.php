<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function sAdminDashboard()
    {
        // Return the 'sAdminDashboard' view
        return view('/admin/sAdminDashboard');
    }

    public function sAdminViewImages()
    {
        // Return the 'view_images' view
        return view('/admin/view_images');
    }

    public function sAdminViewUsers(Request $request)
    {
        $limit = $request->input('limit', 10);
        $filter = $request->input('filter');
        $search = $request->input('search');
    
        $users = User::query()->orderBy('created_at', 'desc');
    
        if ($filter && $search) {
            if ($filter === 'role') {
                if (is_numeric($search)) {
                    // Admin typed a number (e.g. "1"), so search by role = 1
                    $users->where('role', $search);
                } else {
    
                    // Convert to lowercase for easier comparison
                    $searchLower = strtolower($search);
    
                    // Simple mapping logic:
                    if (str_contains($searchLower, 'super')) {
                        // "Super Admin" or "Super Administrator"
                        $users->where('role', 1);
                    } elseif (str_contains($searchLower, 'admin')) {
                        // "Administrator"
                        $users->where('role', 2);
                    } elseif (str_contains($searchLower, 'general')) {
                        // "General User"
                        $users->where('role', 3);
                    } else {
                        // No match found; optionally return no results or skip filter
                        $users->whereRaw('1=0'); // returns empty
                    }
                }
            }
            else {
                $users->where($filter, 'LIKE', '%' . $search . '%');
            }
        }
    
        $users = $users->paginate($limit);
    
        return view('admin.view_users', [
            'users'   => $users,
            'filter'  => $filter,
            'search'  => $search,
            'limit'   => $limit,
        ]);
    }

    public function deleteUser($id)
    {
        // Find the user by ID and delete them
        $user = User::findOrFail($id); // Throws 404 if user not found
        $user->delete();

        // Redirect back to the view with a success message
        return redirect()->route('sAdminViewUsers')->with('success', 'User deleted successfully!');
    }

    public function editUser(Request $request, $id)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $id],
            'role' => ['required', 'integer'],
            'country' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:255'],
        ]);
    
        $user = User::findOrFail($id);
        $user->update($request->all());
    
        return response()->json(['success' => true, 'message' => 'User updated successfully!']);
    }
    
}
