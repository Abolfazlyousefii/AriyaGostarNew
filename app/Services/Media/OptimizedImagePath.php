<?php

namespace App\Services\Media;

use InvalidArgumentException;

final class OptimizedImagePath
{
    public function for(string $sourceKey, string $sourceContents): string
    {
        $sourceKey = ltrim(str_replace('\\', '/', trim($sourceKey)), '/');

        if (
            $sourceKey === ''
            || str_contains($sourceKey, "\0")
            || preg_match('#(^|/)\.\.(/|$)#', $sourceKey)
            || filter_var($sourceKey, FILTER_VALIDATE_URL) !== false
        ) {
            throw new InvalidArgumentException('Unsafe source image key.');
        }

        $directory = pathinfo($sourceKey, PATHINFO_DIRNAME);
        $filename = pathinfo($sourceKey, PATHINFO_FILENAME);
        $hash = substr(hash('sha256', $sourceContents), 0, 12);
        $relative = ($directory === '.' ? '' : $directory . '/') . $filename . '-' . $hash . '.webp';

        return 'optimized/webp/' . $relative;
    }
}
