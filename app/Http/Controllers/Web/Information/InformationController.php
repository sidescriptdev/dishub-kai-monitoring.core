<?php

namespace App\Http\Controllers\Web\Information;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Information;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class InformationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $informations = Information::query()->when($request->get('title') != null, function($query) use($request) {
            $query->where('title', 'like', '%'.$request->get('title').'%');
        })
        ->orderBy('created_at' ,$request->get('sort', 'DESC'))
        ->paginate(10);
        return Inertia::render('Information/Index', ['informations' => $informations]);
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
        $data = [
            'title' => $request->title,
            'link' => $request->link,
            'description' => $request->description,
            'image' => $request->file('image'),
            'document' => $request->file('document'),
        ];
        $rules = [
            'title' => 'required',
            'link' => 'nullable|url',
            'description' => 'nullable',
            'image' => 'nullable|image|max:2048',
            'document' => 'nullable|mimes:pdf,ppt,pptx,doc,docx,xls,xlsx|max:30720'
        ];
        Validator::make($data, $rules)->validate();
        if($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'image-'.Str::slug($request->title).'-'.time().'-'.uniqid().'.'.$image->extension();
            $img = Image::make($image->path());
            $this->checkDirectory('/information/image/');
            $img->resize(750, 750, function($constraint) {
                $constraint->aspectRatio();
            })->save(public_path('/information/image/').$imageName);
            $data['image'] = $imageName;
        } else {
            unset($data['image']);
        }

        if($request->hasFile('document')) {
            $file = $request->file('document');
            $fileName = Str::slug($request->title).'-'.time().'-'.uniqid().'.'.$file->extension();
            $this->checkDirectory('/information/data/');
            $file->move(public_path('/information/data/'), $fileName);
            $data['document'] = $fileName;
        } else {
            unset($data['document']);
        }
        Information::query()->create($data);
        return redirect()->back()->with('message', 'Data informasi berhasil disimpan')->with('status', 'success');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $name = str_replace('-', ' ', $request->get('title'));
        $information = Information::query()->where('id', $id)->orWhere('title', 'LIKE', '%'.$name.'%')->first();
        return Inertia::render('Information/Show', ['information' => $information]);
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
        $data = [
            'title' => $request->title,
            'link' => $request->link,
            'description' => $request->description,
            'image' => $request->file('image'),
            'document' => $request->file('document'),
        ];
        $rules = [
            'title' => 'required',
            'link' => 'nullable|url',
            'description' => 'nullable',
            'image' => 'nullable|image|mimes:jpg,png,jpeg',
            'document' => 'nullable|mimes:pdf,ppt,pptx,doc,docx,xls,xlsx'
        ];
        Validator::make($data, $rules)->validate();
        $information = Information::query()->find($id);
        if($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'image-'.Str::slug($request->title).'-'.time().'-'.uniqid().'.'.$image->extension();
            $img = Image::make($image->path());
            $this->checkDirectory('/information/image/');
            if($information->image != null) {
                if(File::exists(public_path('/information/image/'))) unlink(public_path('/information/image/').$information->image);
            }
            $img->resize(750, 750, function($constraint) {
                $constraint->aspectRatio();
            })->save(public_path('/information/image/').$imageName);
            $data['image'] = $imageName;
        } else {
            unset($data['image']);
        }

        if($request->hasFile('document')) {
            $file = $request->file('document');
            $fileName = Str::slug($request->title).'-'.time().'-'.uniqid().'.'.$file->extension();
            $this->checkDirectory('/information/data/');
            if($information->document != null) {
                if(File::exists(public_path('/information/data/'))) unlink(public_path('/information/data/').$information->document);
            }
            $file->move(public_path('/information/data/'), $fileName);
            $data['document'] = $fileName;
        } else {
            unset($data['document']);
        }
        Information::query()->where('id', $id)->update($data);
        return redirect()->back()->with('message', 'Data informasi berhasil disimpan')->with('status', 'success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $information = Information::query()->find($id);
        if($information->image != null) {
            if(File::exists(public_path('/information/image/'))) unlink(public_path('/information/image/').$information->image);
        }
        if($information->document != null) {
            if(File::exists(public_path('/information/data/'))) unlink(public_path('/information/data/').$information->document);
        }
        $information->delete();
        return redirect()->back()->with('message', 'Data informasi berhasil dihapus')->with('status', 'success');
    }

    public function download(Request $request)
    {
        $information = Information::query()->find($request->get('id'));
        return Response::download(public_path('/information/data/').$information->document);
    }

    public function deleteImage($id)
    {
        $information = Information::query()->find($id);
        if($information->image != null) {
            if(File::exists(public_path('/information/image/'))) unlink(public_path('/information/image/').$information->image);
        }
        $information->image = null;
        $information->save();
        return redirect()->back()->with('message', 'Gambar informasi berhasil dihapus')->with('status', 'success');
    }
}
