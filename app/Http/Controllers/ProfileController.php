<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * عرض جميع البروفايلات (اختياري - حسب الصلاحيات)
     */
    public function index()
    {
        $profiles = Profile::with('user')->paginate(10);
        return response()->json([
            'status' => true,
            'data' => $profiles
        ]);
    }

    /**
     * إنشاء بروفايل جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            "full_name"  => "required|string|max:255",
            "phone"      => "nullable|string|max:20",
            "birth_day"  => "nullable|date",
            "gender"     => "boolean"
        ]);

        $data = $request->only(['full_name', 'phone', 'birth_day', 'gender']);
        $data['user_id'] = $request->user()->id;

        // منع إنشاء أكثر من بروفايل لنفس المستخدم
        if (Profile::where('user_id', $data['user_id'])->exists()) {
            return response()->json([
                'status'  => false,
                'message' => 'Profile already exists for this user'
            ], 409);
        }

        $profile = Profile::create($data);

        return response()->json([
            'status' => true,
            'data'   => $profile
        ], 201);
    }

    /**
     * عرض بروفايل محدد
     */
    public function show($id)
    {
        $profile = Profile::with('user')->find($id);

        if (!$profile) {
            return response()->json([
                'status'  => false,
                'message' => 'Profile not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $profile
        ]);
    }

    /**
     * تحديث البروفايل
     */
    public function update(Request $request, $id)
    {
        $profile = Profile::find($id);

        if (!$profile) {
            return response()->json([
                'status'  => false,
                'message' => 'Profile not found'
            ], 404);
        }

        // تحقق أن المستخدم يملك هذا البروفايل
        if ($request->user()->id !== $profile->user_id) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            "full_name"  => "required|string|max:255",
            "phone"      => "nullable|string|max:20",
            "birth_day"  => "nullable|date",
            "gender"     => "boolean"
        ]);

        $profile->update($request->only(['full_name', 'phone', 'birth_day', 'gender']));

        return response()->json([
            'status' => true,
            'data'   => $profile
        ]);
    }

    /**
     * حذف البروفايل
     */
    public function destroy(Request $request, $id)
    {
        $profile = Profile::find($id);

        if (!$profile) {
            return response()->json([
                'status'  => false,
                'message' => 'Profile not found'
            ], 404);
        }

        // تحقق أن المستخدم يملك هذا البروفايل
        if ($request->user()->id !== $profile->user_id) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $profile->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Profile deleted successfully'
        ]);
    }
}
