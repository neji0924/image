<?php

namespace Neji0924\Image\Http\Controllers;

use File;
use Storage;
use Validator;
use Neji0924\Image\Image;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ImageController extends Controller
{
    public function show(Image $image, $size = null)
    {
        $path = "images/{$image->realname}";
        $origin_path = "images/origin/{$image->realname}";

        if (! is_null($size)) {
            $path = "images/{$size}/{$image->realname}";
        }

        if (! Storage::has($path)) {
            if(! Storage::has($origin_path)) {
                abort(404);
            } else {
                $path = $origin_path;
            }
        }

        $response = response(Storage::get($path));
        $response->header('Content-Type', $image->mime);

        return $response;
    }
}
