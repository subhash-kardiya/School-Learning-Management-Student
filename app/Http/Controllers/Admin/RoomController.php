<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\TeacherMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    private function canPermission(string $permission): bool
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission')) {
            return false;
        }

        return $user->hasPermission($permission);
    }

    public function index(Request $request)
    {
        if (!$this->canPermission('room_manage')) {
            abort(403, 'Unauthorized access');
        }

        if ($request->ajax()) {
            $query = Room::query()->latest();

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    return (int) $row->status === 1
                        ? '<span class="badge bg-success-subtle text-success">Active</span>'
                        : '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex justify-content-end gap-1">
                        <a href="' . route('rooms.edit', $row->id) . '" class="btn btn-sm btn-light" title="Edit">
                            <i class="fas fa-pen"></i>
                        </a>
                        <form action="' . route('rooms.destroy', $row->id) . '" method="POST" onsubmit="return confirm(\'Delete this room?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button class="btn btn-sm btn-light text-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('rooms.index');
    }

    public function create()
    {
        if (!$this->canPermission('room_manage')) {
            abort(403, 'Unauthorized access');
        }

        return view('rooms.create');
    }

    public function store(Request $request)
    {
        if (!$this->canPermission('room_manage')) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:rooms,name',
            'capacity' => 'nullable|integer|min:1|max:5000',
            'status' => 'required|boolean',
        ]);

        Room::create($validated);

        return redirect()->route('rooms.index')->with('success', 'Room created successfully.');
    }

    public function edit($id)
    {
        if (!$this->canPermission('room_manage')) {
            abort(403, 'Unauthorized access');
        }

        $room = Room::findOrFail($id);
        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, $id)
    {
        if (!$this->canPermission('room_manage')) {
            abort(403, 'Unauthorized access');
        }

        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:rooms,name,' . $room->id,
            'capacity' => 'nullable|integer|min:1|max:5000',
            'status' => 'required|boolean',
        ]);

        $room->update($validated);

        return redirect()->route('rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy($id)
    {
        if (!$this->canPermission('room_manage')) {
            abort(403, 'Unauthorized access');
        }

        $room = Room::findOrFail($id);
        if (TeacherMapping::where('room_id', $room->id)->exists()) {
            return redirect()->route('rooms.index')->with('error', 'Room is already assigned in class mapping.');
        }
        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Room deleted successfully.');
    }
}
