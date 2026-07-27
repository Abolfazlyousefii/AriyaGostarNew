<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public $ordering = 1;

    public function store(Request $request)
    {
        $this->validate($request, [
            'title'     => 'required|string|max:255',
            'type'      => 'required|string|in:productcat,postcat',
            'slug'      => 'nullable|unique:categories,slug',
            'menu_icon' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $this->authorizeCategory($request->type);

        $category = Category::create([
            'title'      => $request->title,
            'lang'       => app()->getLocale(),
            'type'       => $request->type,
            'slug'       => $request->slug ?: $request->title,
            'published'  => true,
            'ordering'   => ((int) Category::where('type', $request->type)->max('ordering')) + 1,
            'category_id'=> null,
        ]);

        if ($request->hasFile('menu_icon')) {
            $category->menu_icon = $this->storeMenuIcon($request->file('menu_icon'), $category);
            $category->save();
        }

        $this->clearFrontCategoryCache();

        return response()->json($this->categoryResponse($category));
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
            'title'            => 'required|string|max:255',
            'image'            => 'nullable|image|max:4096',
            'background_image' => 'nullable|image|max:4096',
            'menu_icon'        => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'slug'             => "nullable|unique:categories,slug,$category->id",
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
            $file = $request->file('image');
            $name = uniqid() . '_' . $category->id . '.' . strtolower($file->getClientOriginalExtension());
            $file->storeAs('categories', $name);

            if ($category->image) {
                Storage::disk('public')->delete(ltrim($category->image, '/'));
            }

            $category->image = '/uploads/categories/' . $name;
            $category->save();
        }

        if ($request->hasFile('background_image')) {
            $file = $request->file('background_image');
            $name = uniqid() . '_' . $category->id . '.' . strtolower($file->getClientOriginalExtension());
            $file->storeAs('categories', $name);

            if ($category->background_image) {
                Storage::disk('public')->delete(ltrim($category->background_image, '/'));
            }

            $category->background_image = '/uploads/categories/' . $name;
            $category->save();
        }

        if ($request->hasFile('menu_icon')) {
            $this->deleteMenuIcon($category->menu_icon);
            $category->menu_icon = $this->storeMenuIcon($request->file('menu_icon'), $category);
            $category->save();
        }

        $this->clearFrontCategoryCache();

        return response()->json($this->categoryResponse($category->fresh()));
    }

    public function destroy(Category $category)
    {
        $this->authorizeCategory($category->type);

        foreach (Category::whereIn('id', $category->allChildCategories())->get() as $childCategory) {
            if ($childCategory->image) {
                Storage::disk('public')->delete(ltrim($childCategory->image, '/'));
            }

            if ($childCategory->background_image) {
                Storage::disk('public')->delete(ltrim($childCategory->background_image, '/'));
            }

            $this->deleteMenuIcon($childCategory->menu_icon);
            $childCategory->menus()->detach();
            $childCategory->delete();
        }

        if ($category->image) {
            Storage::disk('public')->delete(ltrim($category->image, '/'));
        }

        if ($category->background_image) {
            Storage::disk('public')->delete(ltrim($category->background_image, '/'));
        }

        $this->deleteMenuIcon($category->menu_icon);
        $category->menus()->detach();
        $category->delete();

        $this->clearFrontCategoryCache();

        toastr()->success('دسته‌بندی با موفقیت حذف شد.');

        return redirect()->route(
            $category->type == 'productcat'
                ? 'admin.products.categories.index'
                : 'admin.posts.categories.index'
        );
    }

    public function sort(Request $request)
    {
        $this->validate($request, [
            'categories' => 'required|array',
            'type'       => 'required|in:productcat,postcat',
        ]);

        $this->authorizeCategory($request->type);

        $this->ordering = 1;
        $this->sortCategory($request->categories);
        $this->clearFrontCategoryCache();

        return response()->json(['message' => 'ترتیب دسته‌بندی‌ها ذخیره شد.']);
    }

    private function sortCategory(array $categories, $categoryId = null): void
    {
        foreach ($categories as $item) {
            $category = Category::find($item['id']);

            if (!$category) {
                continue;
            }

            $category->update([
                'category_id' => $categoryId,
                'ordering'    => $this->ordering++,
            ]);

            if (!empty($item['children']) && is_array($item['children'])) {
                $this->sortCategory($item['children'], $category->id);
            }
        }
    }

    private function authorizeCategory($type)
    {
        switch ($type) {
            case 'postcat':
                $this->authorize('posts.category');
                break;
            case 'productcat':
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

    private function clearFrontCategoryCache(): void
    {
        Cache::forget('front.productcats');
        Cache::forget('front.productcats.megamenu');
        Cache::forget('front.productcats.megamenu.v2');
    }

    private function categoryResponse(Category $category): array
    {
        return [
            'id'            => $category->id,
            'title'         => $category->title,
            'slug'          => $category->slug,
            'published'     => (bool) $category->published,
            'menu_icon'     => $category->menu_icon,
            'menu_icon_url' => $category->menu_icon ? asset($category->menu_icon) : null,
        ];
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
