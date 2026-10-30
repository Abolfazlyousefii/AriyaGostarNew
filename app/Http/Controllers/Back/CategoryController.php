<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Rules\SupportedImage;
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
            'image' => ['nullable', 'file', 'max:6144', new SupportedImage()],
            'background_image' => ['nullable', 'file', 'max:6144', new SupportedImage()],
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

        foreach (['image', 'background_image'] as $column) {
            if ($request->hasFile($column)) {
                $this->replaceCategoryImage($request->file($column), $category, $column);
            }
        }

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
            'image'            => ['nullable', 'file', 'max:6144', new SupportedImage()],
            'background_image' => ['nullable', 'file', 'max:6144', new SupportedImage()],
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

        foreach (['image', 'background_image'] as $column) {
            if ($request->hasFile($column)) {
                $this->replaceCategoryImage($request->file($column), $category, $column);
            }
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

    /**
     * Always write web-readable category images to public/uploads/categories.
     * Never depend on the host's default filesystem disk or filename extension.
     */
    private function replaceCategoryImage(\Illuminate\Http\UploadedFile $file, Category $category, string $column): void
    {
        if (!in_array($column, ['image', 'background_image'], true)) {
            throw new \InvalidArgumentException('Invalid category image field.');
        }

        $mime = (string) $file->getMimeType();
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp',
            'image/x-ms-bmp' => 'bmp',
            'image/avif' => 'avif',
        ];
        $convert = in_array($mime, ['image/heic', 'image/heif', 'image/tiff', 'image/x-tiff'], true);
        $extension = $convert ? 'webp' : ($extensions[$mime] ?? null);

        if ($extension === null || (!$convert && @getimagesize($file->getRealPath()) === false)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $column => 'فرمت تصویر معتبر نیست. JPG، PNG، WebP، GIF، BMP و AVIF پشتیبانی می‌شوند.',
            ]);
        }

        $name = bin2hex(random_bytes(16)) . '_' . $category->id . '.' . $extension;
        $key = 'categories/' . $name;
        $disk = Storage::disk('uploads');

        if ($convert) {
            if (!extension_loaded('imagick')) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $column => 'تبدیل تصویر HEIC/HEIF/TIFF نیازمند Imagick است.',
                ]);
            }
            try {
                $image = new \Imagick($file->getRealPath());
                $image->setIteratorIndex(0);
                $image->setImageFormat('webp');
                $contents = $image->getImageBlob();
                $image->clear();
                $stored = $disk->put($key, $contents, 'public');
            } catch (\Throwable $e) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $column => 'تبدیل تصویر در سرور ناموفق بود. یک فایل JPG یا WebP بارگذاری کنید.',
                ]);
            }
        } else {
            $stored = $file->storeAs('categories', $name, 'uploads');
        }

        if (!$stored || !$disk->exists($key)) {
            throw new \RuntimeException('فایل تصویر دسته‌بندی در پوشه عمومی ذخیره نشد.');
        }

        $old = (string) ($category->{$column} ?? '');
        $category->{$column} = '/uploads/' . $key;

        try {
            $category->save();
        } catch (\Throwable $e) {
            $disk->delete($key);
            throw $e;
        }

        // Only delete files within the controlled category uploads directory.
        $previous = ltrim(str_replace('\\', '/', $old), '/');
        if (preg_match('#^uploads/categories/[a-zA-Z0-9_.-]+$#D', $previous)) {
            $disk->delete(substr($previous, strlen('uploads/')));
        }
    }
    private function storeMenuIcon($file, Category $category): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $name = uniqid('menu_', true) . '_' . $category->id . '.' . $extension;
        $stored = $file->storeAs('categories/menu-icons', $name, 'uploads');
        if (!$stored || !Storage::disk('uploads')->exists('categories/menu-icons/' . $name)) {
            throw new \RuntimeException('ذخیره آیکن دسته‌بندی ناموفق بود.');
        }

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
