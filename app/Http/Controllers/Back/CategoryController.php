<?php

namespace App\Http\Controllers\Back;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public $ordering = 1;

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string',
            'type'  => 'required|string|in:productcat,postcat',
            'slug'      => 'nullable|unique:categories,slug',
            'menu_icon' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
        ]);

        $this->authorizeCategory($request->type);

        $category = Category::create([
            'title' => $request->title,
            'lang'  => app()->getLocale(),
            'type'  => $request->type,
            'slug'  => $request->slug ?: $request->title,
        ]);

        if ($request->hasFile('menu_icon')) {
            $category->menu_icon = $this->storeMenuIcon($request->file('menu_icon'), $category);
            $category->save();
        }

        Cache::forget('front.productcats');

        return response()->json($category);
    }

    public function edit(Category $category)
    {
        $this->authorizeCategory($category->type);

        if ($category->type == 'productcat') {
            return view('back.products.categories.edit', compact('category'));
        }

        return view('back.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorizeCategory($category->type);

        $this->validate($request, [
            'title' => 'required|string',
            'image'     => 'nullable|image|max:4096',
            'menu_icon' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'slug'      => "nullable|unique:categories,slug,$category->id",
        ]);

        $category->update([
            'title'            => $request->title,
            'slug'             => $request->slug ?: $request->title,
            'meta_title'       => $request->meta_title,
            'meta_description' => $request->meta_description,
            'description'      => $request->description,
            'filter_type'      => $request->filter_type ?: 'inherit',
            'filter_id'        => $request->filter_id,
            'published'        => $request->has('published'),
        ]);

        if ($request->hasFile('image')) {
            $file = $request->image;
            $name = uniqid() . '_' . $category->id . '.' . $file->getClientOriginalExtension();
            $request->image->storeAs('categories', $name);

            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $category->image = '/uploads/categories/' . $name;
            $category->save();
        }

        if ($request->hasFile('background_image')) {
            $file = $request->background_image;
            $name = uniqid() . '_' . $category->id . '.' . $file->getClientOriginalExtension();
            $request->background_image->storeAs('categories', $name);

            if ($category->background_image) {
                Storage::disk('public')->delete($category->background_image);
            }

            $category->background_image = '/uploads/categories/' . $name;
            $category->save();
        }

        if ($request->hasFile('menu_icon')) {
            $this->deleteMenuIcon($category->menu_icon);
            $category->menu_icon = $this->storeMenuIcon($request->file('menu_icon'), $category);
            $category->save();
        }

        Cache::forget('front.productcats');

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $this->authorizeCategory($category->type);

        foreach (Category::whereIn('id', $category->allChildCategories())->get() as $child_category) {
            if ($child_category->image) {
                Storage::disk('public')->delete($child_category->image);
            }
            if ($child_category->background_image) {
                Storage::disk('public')->delete($child_category->background_image);
            }
            $this->deleteMenuIcon($child_category->menu_icon);
            $child_category->menus()->detach();
            $child_category->delete();
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        if ($category->background_image) {
            Storage::disk('public')->delete($category->background_image);
        }
        $this->deleteMenuIcon($category->menu_icon);
        $category->menus()->detach();
        $category->delete();

        Cache::forget('front.productcats');

        toastr()->success('دسته‌بندی با موفقیت حذف شد.');

        return redirect()->route($category->type == 'productcat' ? 'admin.products.categories.index' : 'admin.posts.categories.index');
    }

    public function sort(Request $request)
    {
        $this->validate($request, [
            'categories' => 'required|array',
            'type'       => 'required|in:productcat,postcat',
        ]);

        $this->authorizeCategory($request->type);

        $categories = $request->categories;

        $this->sort_category($categories);
        Cache::forget('front.productcats');

        return response()->json('success');
    }
    private function sort_category($categories, $category_id = null)
    {
        foreach ($categories as $category) {
            Category::find($category['id'])->update(['category_id' => $category_id, 'ordering' => $this->ordering++]);
            if (array_key_exists('children', $category)) {
                $this->sort_category($category['children'], $category['id']);
            }
        }
    }

    private function authorizeCategory($type)
    {
        switch ($type) {
            case "postcat":
                $this->authorize('posts.category');
                break;
            case "productcat":
                $this->authorize('products.category');
                break;
        }
    }

    private function storeMenuIcon($file, Category $category): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $name = uniqid('menu_', true) . '_' . $category->id . '.' . $extension;
        $file->storeAs('categories/menu-icons', $name);

        return '/uploads/categories/menu-icons/' . $name;
    }

    private function deleteMenuIcon(?string $path): void
    {
        if (!$path) {
            return;
        }

        Storage::disk('public')->delete(ltrim($path, '/'));
    }

    public function generate_slug(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $slug = SlugService::createSlug(Category::class, 'slug', $request->title);

        return response()->json(['slug' => $slug]);
    }
}
