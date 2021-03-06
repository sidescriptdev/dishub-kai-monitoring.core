<?php

namespace App\Http\Controllers\Web\Monitoring;

use App\Http\Controllers\Controller;
use App\Jobs\Monitoring\Category\CreateData;
use App\Jobs\Monitoring\Category\EditData;
use App\Models\Monitoring\Category;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::query()->with(['input' => function($query) {
            $query->where('monitoring_object_id', null);
        }])->get();
        return Inertia::render('Monitoring/Category/Index', ['categories' => $categories]);
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
        Category::query()->create($data);
        return redirect()->back()->with('message', 'Data Kategori baru berhasil disimpan')->with('status', 'success');
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
        $category = Category::query()->find($id);
        if($request->hasFile('icon')) {
            $file = $request->file('icon');
            $filename = 'icon-'.Str::slug($request->name).'-'.uniqid().'.'.$file->extension();
            $image = Image::make($file->path());
            $this->checkDirectory('/monitoring/icon');
            if($category->icon != null) {
                if(File::exists(public_path('/monitoring/icon/').$category->icon)) {
                    File::delete(public_path('/monitoring/icon/').$category->icon);
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
        $category->update($data);
        return redirect()->back()->with('message', 'Data kategori berhasil diedit')->with('status', 'success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        if($category->icon != null) {
            if(File::exists(public_path('/monitoring/icon/').$category->icon)) {
                File::delete(public_path('/monitoring/icon/').$category->icon);
            }
        }
        $category->delete();
        return redirect()->back()->with('message', 'Data kategori berhasil dihapus')->with('status', 'success');
    }

    public function deleteImage($id)
    {
        $category = Category::find($id);
        if($category->icon != null) {
            if(File::exists(public_path('/monitoring/icon/').$category->icon)) {
                File::delete(public_path('/monitoring/icon/').$category->icon);
            }
        }
        $category->icon = null;
        $category->save();
        return redirect()->back()->with('message', 'Gambar kategori berhasil dihapus')->with('status', 'success');
    }
}
