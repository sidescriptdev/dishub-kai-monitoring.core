<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Employee\CreateData;
use App\Jobs\Employee\EditData;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\Empty_;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $employees = Employee::query()->with('team', 'monitoring')
        ->when($request->get('name') != null, function($query) use($request) {
            $query->where('name', 'LIKE', '%'.$request->get('name').'%');
        })
        ->when($request->get('team_id') != null, function($query) use($request) {
            $query->whereHas('team', function($queryTeam) use($request) {
                $queryTeam->where('employee_teams.team_id', $request->get('team_id'));
            });
        })
        ->orderBy('created_at', $request->get('sort', 'ASC'))
        ->get();
        return $this->jsonResponse($employees);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required',
            'phone_number' => 'required',
            'position' => 'required',
            'profession' => 'required',
        ];
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'division' => $request->division,
            'branch' => $request->branch,
            'position' => $request->position,
            'profession' => $request->profession,
        ];
        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            return $this->jsonResponse([
                'messages' => $validator->errors(),
            ], 400, 'FAILED');
        }
        $employee = Employee::query()->create($data);
        return $this->jsonResponse($employee);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $employee = Employee::query()->with('team', 'monitoring')->find($id);
        return $this->jsonResponse($employee);
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
        $rules = [
            'name' => 'required',
            'email' => 'required',
            'phone_number' => 'required',
            'position' => 'required',
            'profession' => 'required',
        ];
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'division' => $request->division,
            'branch' => $request->branch,
            'position' => $request->position,
            'profession' => $request->profession,
        ];
        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            return $this->jsonResponse([
                'messages' => $validator->errors(),
            ], 400, 'FAILED');
        }
        Employee::query()->where('id', $id)->update($data);
        $employee = Employee::query()->find($id);
        return $this->jsonResponse($employee);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $employee = Employee::query()->find($id);
        $employee->delete();
        return $this->jsonResponse($employee);
    }
}
