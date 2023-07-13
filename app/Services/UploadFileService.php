<?php

namespace App\Services;

use Illuminate\Support\Str;

class UploadFileService {

    /**
     * Save file to public/upload folder
     *
     * @param string $destination_path
     * @param file $file
     * 
     * @return void
     */
    public function save(string $destination, $file) {
        // Generate unique file name
        $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();

        // Move file to public/upload folder
        $path = '/upload/' . $destination . '/';
        $file->move(app()->basePath('public') . $path, $fileName);

        // Get file path
        $filePath = $path . $fileName;

        return $filePath;
    }

}

