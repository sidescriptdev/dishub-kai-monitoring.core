<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function indexLogin(Request $request)
    {
        $data = [
            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'code' => $request->get('code'),
            'remember' => $request->get('remember', 'on'),
            'menu' => $request->get('menu'),
        ];
        if($data['email'] != null && $data['password'] != null) {
            $data_login = [
                'email' => $data['email'],
                'password' => $data['password'],
            ];
            if(Auth::attempt($data_login, $data['remember'])) {
                $user = User::query()->find(Auth::id());
                return redirect()->route('index.verification', ['code' => $request->code, 'menu' => $request->menu])->with('message', 'Login berhasil: Verifikasi dahulu')
                    ->with('status', 'success');;
            }
        }
            // return redirect()->action([UserController::class, 'login'], ['email' => $data['email'], 'password' => $data['password'], 'code' => $data['code'], 'remember' => $data['remember'], 'menu' => $data['menu']]);

        return Inertia::render('Auth/Login', ['data_login' => $data]);
    }

    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password,
        ];
        Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if(Auth::attempt($data, $request->remember)) {
            $user = User::query()->find(Auth::id());
            return redirect()->route('index.verification', ['code' => $request->code, 'menu' => $request->menu])->with('message', 'Login berhasil: Verifikasi dahulu')
                ->with('status', 'success');;
        } else {
            return redirect()->back()->with('message', 'Login gagal: Email atau kata sandi tidak sesuai')
                ->with('status', 'failed');
        }
    }

    public function logout(Request $request)
    {
        $user = User::query()->find(Auth::id());
        // $user->verified_at = null;
        // $user->save();
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flush();
        return redirect()->route('home', ['reload' => true])->with('message', 'Logout berhasil')->with('status', 'success');
    }

    public function indexVerification(Request $request)
    {
        $data = [
            'code' => $request->get('code'),
            'menu' => $request->get('menu'),
        ];
        $user = User::query()->find(Auth::id());
        if($data['code'] != null) {
            $user = User::query()->find(Auth::id());
            if($user->code == $data['code']) {
                return redirect()->route('dashboard', ['menu' => $data['menu']])->with('message', 'Verifikasi Berhasil : Selamat datang kembali')->with('status', 'success');
                } else {
                return redirect()->route('index.verification')->with('message', 'Kode verifikasi tidak sesuai')->with('status', 'failed');
            }
        }
        return Inertia::render('Auth/Verify', ['user' => $user, 'data_login' => $data]);
    }

    public function verification(Request $request)
    {
        $data = [
            'code' => $request->code != null ? $request->code : '',
            'menu' => $request->menu != null ? $request->menu : '',
        ];
        Validator::make($data, [
            'code' => 'required',
        ]);
        $user = User::query()->find(Auth::id());
        if($user->code == $request->code) {
            return redirect()->route('dashboard', ['menu' => $data['menu']])->with('message', 'Verifikasi Berhasil : Selamat datang kembali')->with('status', 'success');
            } else {
            return redirect()->route('index.verification')->with('message', 'Kode verifikasi tidak sesuai')->with('status', 'failed');
        }
    }
}
