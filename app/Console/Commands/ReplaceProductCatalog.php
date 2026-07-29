<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReplaceProductCatalog extends Command
{
    /**
     * نام فرمان و ورودی‌های آن
     */
    protected $signature = 'products:replace-catalog
                            {file : مسیر فایل CSV}
                            {--dry-run : فقط بررسی فایل، بدون تغییر دیتابیس}';

    /**
     * توضیح فرمان
     */
    protected $description = 'بررسی و جایگزینی کاتالوگ محصولات از طریق فایل CSV';

    /**
     * اجرای فرمان
     */
    public function handle(): int
    {
        $relativePath = (string) $this->argument('file');
        $absolutePath = base_path($relativePath);

        if (! is_file($absolutePath)) {
            $this->error('فایل CSV پیدا نشد.');
            $this->line('مسیر بررسی‌شده:');
            $this->line($absolutePath);

            return self::FAILURE;
        }

        if (! is_readable($absolutePath)) {
            $this->error('فایل پیدا شد، اما قابل خواندن نیست.');

            return self::FAILURE;
        }

        $this->info('فرمان products:replace-catalog با موفقیت ثبت شده است.');
        $this->line('فایل پیدا شد:');
        $this->line($absolutePath);

        if ($this->option('dry-run')) {
            $this->warn('حالت آزمایشی فعال است؛ هیچ تغییری در دیتابیس انجام نشد.');
        }

        /*
         * منطق اصلی خواندن CSV، گروه‌بندی تنوع‌ها و جایگزینی
         * محصولات بعد از بررسی ساختار مدل‌ها در این قسمت اضافه می‌شود.
         */

        return self::SUCCESS;
    }
}