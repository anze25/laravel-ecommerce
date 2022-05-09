<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminProfileController extends Controller
{
    public function AdminProfile()
    {
        $adminData = Admin::find(1);
        return view('admin.admin_profile_view', compact('adminData'));
    }

    public function AdminProfileEdit()
    {
        $editData = Admin::find(1);
        return view('admin.admin_profile_edit', compact('editData'));
    }

    public function AdminChangePassword()
    {

        return view('admin.admin_change_password');
    }

    public function AdminUpdatePassword(Request $request)
    {

        $validateData = $request->validate([
            'oldpassword' => 'required',
            'password'  => 'required|confirmed',
        ]);

        $hashedPassword = Admin::find(1)->password;
        if (Hash::check($request->oldpassword, $hashedPassword)) {
            $admin = Admin::find(1);
            $admin->password = Hash::make($request->password);
            $admin->save();
            Auth::logout();
            return redirect()->route('admin.logout');
        } else {
            return redirect()->back();
        }
    }   // end method

    public function AdminProfileStore(Request $request)
    {
        $data = Admin::find(1);
        $data->name = $request->name;
        $data->email = $request->email;

        if ($request->file('profile_photo_path')) { # Če je izbran image
            $file = $request->file('profile_photo_path');
            @unlink(public_path('upload/admin_images/' . $data->profile_photo_path));   # Odstrani prejšnjo datoteko.
            $filename = date('YmdHi') . $file->getClientOriginalName();                 # Ime je datastring + original ime s končnico
            $file->move(public_path('upload/admin_images'), $filename);                 # Image se shrani v določeno mapo z prej določenim imenom
            $data['profile_photo_path'] = $filename;                                    # V bazi se ime shrani pod Profile photo path
        }

        $data->save();

        $notification = array(
            'message' => 'Admin Profile Updated Succesfully',
            'alert-type' => 'success',
        );


        return redirect()->route('admin.profile')->with($notification);
    }
}
