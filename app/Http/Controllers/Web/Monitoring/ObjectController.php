<?php

namespace App\Http\Controllers\Web\Monitoring;

use App\Http\Controllers\Controller;
use App\Jobs\Monitoring\Object\CreateData;
use App\Jobs\Monitoring\Object\EditData;
use App\Models\Monitoring\Category;
use Intervention\Image\Facades\Image;
use App\Models\Monitoring\ObjectData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ObjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $categoryId = $request->get('categoryId', Category::query()->first()->id);
        $objects = ObjectData::with(['input' => function($query) {
            $query->where('monitoring_id', null);
        }])->get();
        // return response()->json($objects);
        $category = Category::query()->with('input')->find($categoryId);
        return Inertia::render('Monitoring/Object/Index', [
            'objects' => $objects,
            'category' => $category,
        ]);
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
        ];
        $rules = [
            'name' => 'required',
            'icon' => 'nullable|image|max:2048',
        ];
        Validator::make($data, $rules)->validate();
        if($request->hasFile('icon')) {
            $file = $request->file('icon');
            $filename = 'icon-'.Str::slug($request->name).'-'.uniqid().'.'.$file->extension();
            $image = Image::make($file->path());
            $this->checkDirectory('/monitoring/icon');
            $image->resize(750, 750, function($constraint) {
                $constraint->aspectRatio();
            })->save(public_path('/monitoring/icon/'.$filename));
            $data['icon'] = $filename;
        } else {
            unset($data['icon']);
        }
        CreateData::dispatch($data);
        ObjectData::query()->create($data);
        return redirect()->back()->with('message', 'Data objek baru berhasil disimpan')->with('status', 'success');
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
        ];
        $rules = [
            'name' => 'required',
            'icon' => 'nullable|image|max:2048',
        ];
        Validator::make($data, $rules)->validate();
        $object = ObjectData::query()->find($id);
        if($request->hasFile('icon')) {
            $file = $request->file('icon');
            $filename = 'icon-'.Str::slug($request->name).'-'.uniqid().'.'.$file->extension();
            $image = Image::make($file->path());
            $this->checkDirectory('/monitoring/icon');
            if($object->icon != null) {
                if(File::exists(public_path('/monitoring/icon/').$object->icon)) {
                    File::delete(public_path('/monitoring/icon/').$object->icon);
                }
            }
            $image->resize(750, 750, function($constraint) {
                $constraint->aspectRatio();
            })->save(public_path('/monitoring/icon/'.$filename));
            $data['icon'] = $filename;
        } else {
            unset($data['icon']);
        }
        EditData::dispatch($data);
        $object->update($data);
        return redirect()->back()->with('message', 'Data objek berhasil diedit')->with('status', 'success');
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
        if($object->icon != null) {
            if(File::exists(public_path('/monitoring/icon/').$object->icon)) {
                File::delete(public_path('/monitoring/icon/').$object->icon);
            }
        }
        $object->delete();
        return redirect()->back()->with('message', 'Data objek berhasil dihapus')->with('status', 'success');
    }

    public function deleteImage($id)
    {
        $object = ObjectData::find($id);
        if($object->icon != null) {
            if(File::exists(public_path('/monitoring/icon/').$object->icon)) {
                File::delete(public_path('/monitoring/icon/').$object->icon);
            }
        }
        $object->icon = null;
        $object->save();
        return redirect()->back()->with('message', 'Data objek berhasil dihapus')->with('status', 'success');
    }
}
