<?php

namespace App\Http\Controllers\Api\Monitoring;

use App\Http\Controllers\Controller;
use App\Jobs\Monitoring\Object\CreateData;
use App\Models\Monitoring\ObjectData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ObjectDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = ObjectData::query()->with('category', 'categoryObject', 'categoryObject.monitoring');
        $objects = $query->when($request->category_id != null, function($queryObject, $request) {
            $queryObject->whereHas('category', function($object) use($request) {
                $object->where('monitoring_category_id', $request->get('category_id', 1));
            });
        })->get();
        return $this->jsonResponse($objects);
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
            'icon' => $request->file('icon'),
            'description' => $request->description,
        ];
        CreateData::dispatch($data);
        if($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $iconName = 'object-'.Str::slug($request->name).'-'.uniqid().'.'.$icon->extension();
            $this->checkDirectory('/monitoring/icon');
            $icon->move(public_path('/monitoring/icon/'), $iconName);
            $data['icon'] = $iconName;
        }
        $object = ObjectData::query()->create($data);
        return $this->jsonResponse($object);
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
            'icon' => $request->file('icon'),
            'description' => $request->description,
        ];
        CreateData::dispatch($data);
        if($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $iconName = 'object-'.Str::slug($request->name).'-'.uniqid().'.'.$icon->extension();
            $this->checkDirectory('/monitoring/icon');
            $icon->move(public_path('/monitoring/icon/'), $iconName);
            $data['icon'] = $iconName;
        }
        $object = ObjectData::query()->where('id', $id)->update($data);
        return $this->jsonResponse($object);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $object = ObjectData::find($id);
        if(File::exists(public_path('/monitoring/icon/').$object->icon)) {
            File::delete(public_path('/monitoring/icon/').$object->icon);
        }
        $object->delete();
        return $this->jsonResponse($object);
    }
}
