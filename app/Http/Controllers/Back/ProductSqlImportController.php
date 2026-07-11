<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductSqlImportController extends Controller
{
    public function create()
    {
        return view('back.products.sql-import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt|max:51200',
        ]);

        $sql = File::get($request->file('sql_file')->getRealPath());

        DB::beginTransaction();

        try {
            DB::unprepared($sql);

            DB::commit();

            if (class_exists(\App\Models\Product::class) && method_exists(\App\Models\Product::class, 'clearCache')) {
                \App\Models\Product::clearCache();
            }

            toastr()->success('فایل SQL محصولات با موفقیت ایمپورت شد.');

            return redirect()->route('admin.products.index');
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            toastr()->error('خطا در ایمپورت فایل SQL: ' . $e->getMessage());

            return back()->withInput();
        }
    }
}