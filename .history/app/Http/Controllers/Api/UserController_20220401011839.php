<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\User\CreateData;
use App\Jobs\User\EditData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->get('name'), function($query) use($request) {
                $query->where('name', 'LIKE', '%'.$request->get('name').'%');
            })->orderBy('created_at', $request->get('sort', 'ASC'))
            ->paginate($request->get('pagination', 10));
        return $this->jsonResponse($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'level' => $request->level,
        ];
        CreateData::dispatch($data);
        $user = User::query()->create($data);
        return $this->jsonResponse($user);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::query()->find($id);
        return $this->jsonResponse($user);
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
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'level' => $request->level,
        ];
        EditData::dispatch($data);
        $user = User::query()->where('id', $id)->update($data);
        return $this->jsonResponse($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::query()->find($id);
        $user->delete();
        return $this->jsonResponse($user);
    }

    public function login(Request $request)
    {
        if(Auth::guard('users-api')->attempt($request->only('email', 'password'))) {
            $user = Auth::guard('users-api')->user();
            return $this->jsonResponse($user);
        }
    }
}
