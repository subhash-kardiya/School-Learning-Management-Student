<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show()
    {
        $role = session('role');
        $user = Auth::user();

        return view('profile.show', compact('user', 'role'));
    }

    public function update(Request $request)
    {
        $role = session('role');
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized access');
        }

        $rules = [
            'full_name' => 'required|string|max:255',
            'mobile_no' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];

        $data = $request->validate($rules);

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $filename = uniqid('profile_') . '.' . $file->getClientOriginalExtension();
            $folder = 'uploads/profiles';

            if ($role === 'teacher') {
                $folder = 'uploads/teachers';
            } elseif ($role === 'student') {
                $folder = 'uploads/students';
            } elseif ($role === 'parent') {
                $folder = 'uploads/parents';
            } elseif ($role === 'admin' || $role === 'superadmin') {
                $folder = 'uploads/admins';
            }

            $file->move(public_path($folder), $filename);
            $data['profile_image'] = $filename;
        }

        if ($role === 'admin' || $role === 'superadmin') {
            $user->admin_name = $data['full_name'];
            if (array_key_exists('mobile_no', $data)) {
                $user->mobile_no = $data['mobile_no'];
            }
            if (array_key_exists('address', $data)) {
                $user->address = $data['address'];
            }
            if (array_key_exists('date_of_birth', $data)) {
                $user->dob = $data['date_of_birth'];
            }
            if (array_key_exists('profile_image', $data)) {
                $user->profile_image = $data['profile_image'];
            }
        } elseif ($role === 'teacher') {
            $user->name = $data['full_name'];
            $user->mobile_no = $data['mobile_no'] ?? $user->mobile_no;
            $user->address = $data['address'] ?? $user->address;
            $user->date_of_birth = $data['date_of_birth'] ?? $user->date_of_birth;
            if (array_key_exists('profile_image', $data)) {
                $user->profile_image = $data['profile_image'];
            }
        } elseif ($role === 'student') {
            $user->student_name = $data['full_name'];
            $user->mobile_no = $data['mobile_no'] ?? $user->mobile_no;
            $user->address = $data['address'] ?? $user->address;
            $user->date_of_birth = $data['date_of_birth'] ?? $user->date_of_birth;
            if (array_key_exists('profile_image', $data)) {
                $user->profile_image = $data['profile_image'];
            }
        } elseif ($role === 'parent') {
            $user->parent_name = $data['full_name'];
            $user->mobile_no = $data['mobile_no'] ?? $user->mobile_no;
            $user->address = $data['address'] ?? $user->address;
            $user->date_of_birth = $data['date_of_birth'] ?? $user->date_of_birth;
            if (array_key_exists('profile_image', $data)) {
                $user->profile_image = $data['profile_image'];
            }
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }
}
