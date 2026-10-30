<?php

namespace App\Http\Controllers\Back;

use App\Models\Banner;
use App\Http\Controllers\Controller;
use App\Services\Media\ImageStorageService;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Banner::class, 'banner');
    }

    public function index()
    {
        $banners = Banner::detectLang()->orderBy('ordering')->get();

        return view('back.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('back.banners.create');
    }

    public function store(Request $request, ImageStorageService $imageStorage)
    {
        $this->validate($request, [
            'image'       => 'image|required|max:2048',
            'group'       => 'required',
            'title'       => 'nullable',
            'description' => 'nullable',
        ]);

        $path = $imageStorage->store(
            $request->file('image'),
            'banners'
        );

        Banner::create([
            'link'        => $request->link,
            'lang'        => app()->getLocale(),
            'group'       => $request->group,
            'published'   => $request->published ? true : false,
            'image'       => $path,
            'title'       => $request->title,
            'description' => $request->description,
        ]);

        toastr()->success('بنر با موفقیت ایجاد شد.');

        return response("success");
    }

    public function edit(Banner $banner)
    {
        return view('back.banners.edit', compact('banner'));
    }

    public function update(Banner $banner, Request $request, ImageStorageService $imageStorage)
    {
        $this->validate($request, [
            'image' => 'image|max:2048',
            'group' => 'required'
        ]);

        if ($request->hasFile('image')) {

            $path = $imageStorage->store(
                $request->file('image'),
                'banners',
                $banner->image
            );

            $banner->image = $path;
            $banner->save();
        }

        $banner->update([
            'link'        => $request->link,
            'group'       => $request->group,
            'published'   => $request->published ? true : false,
            'title'       => $request->title,
            'description' => $request->description,
        ]);

        toastr()->success('بنر با موفقیت ویرایش شد.');

        return response("success");
    }

    public function destroy(Banner $banner, ImageStorageService $imageStorage)
    {
        if ($banner->image) {
            $imageStorage->delete($banner->image);
        }

        $banner->delete();

        return response('success');
    }

    public function sort(Request $request)
    {
        $this->authorize('banners.update');

        $this->validate($request, [
            'banners' => 'required|array'
        ]);

        $i = 1;

        foreach ($request->banners as $banner) {
            Banner::findOrFail($banner)->update([
                'ordering' => $i++,
            ]);
        }

        return response('success');
    }
}