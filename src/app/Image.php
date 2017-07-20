<?php

namespace Neji0924\Image;

use File;
use Storage;
use Tinify\Tinify;
use Tinify\Source;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use SoftDeletes;

    protected $fillable = ['filename', 'realname', 'mime', 'size'];

    protected $casts = [
        'extras' => 'object',
    ];

    public function target()
    {
        return $this->morphTo();
    }

    public static function upload($file, $target = null, $extras = null)
    {
        $realname = md5(time() . uniqid());

        $origin_dir = file_exists(storage_path("app/images/origin"));
        if (!$origin_dir) {
            File::makeDirectory(storage_path("app/images/origin"), $mode = 755, true, true);
        }

        $thumb_dir = file_exists(storage_path("app/images/thumbnail"));
        if (!$thumb_dir) {
            File::makeDirectory(storage_path("app/images/thumbnail"), $mode = 755, true, true);
        }

        Storage::put("images/origin/{$realname}", File::get($file));

        $image = new static([
            'filename' => $file->getClientOriginalName(),
            'realname' => $realname,
            'mime'     => $file->getClientMimeType(),
            'size'     => $file->getClientSize(),
        ]);

        if (! is_null($extras)) {
            $image->extras = $extras;
        }

        if (! is_null($target)) {
            $image->target()->associate($target);
        }

        $image->save();

        $image->resizeImg(getimagesize($file), $realname);
        $image->compression($image->realname);

        return $image;
    }

    public function compression($realname)
    {
        if (! is_null(env('TINYPNG_KEY', null))) {
            Tinify::setKey(env('TINYPNG_KEY', null));
            $compression = Source::fromFile(storage_path("app/images/origin/{$realname}"))->toFile(storage_path("app/images/{$realname}"));
            return $compression;
        } else {
            return false;
        }
    }

    public function resizeImg($imgsize, $realname)
    {
        $filepath = storage_path("app/images/origin/{$realname}");
        $orig_w = $imgsize[0];
        $orig_h = $imgsize[1];

        if ($orig_w > 450 || $orig_h > 450) {
            if ($orig_w > $orig_h) {
                $thumb_w = 450;
                $persent = $thumb_w / $orig_w;
                $thumb_h = $orig_h * $persent;
            } else {
                $thumb_h = 450;
                $persent = $thumb_h / $orig_h;
                $thumb_w = $orig_w * $persent;
            }

            /**
             *   function getImageSize($image)
             * 
             *   return array(
             *     0          => width,
             *     1          => height,
             *     2          => type,
             *     3          => width="xxx" height="xxx",
             *     'bits'     => '大小',
             *     'channels' => '通道RGB默認是3',
             *     'mime'     =>  mime
             *   )
             * 
             *   type:
             *     1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，
             *     7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，
             *     9 = JPC，10 = JP2，11 = JPX，12 = JB2，13 = SWC，14 = IFF，
             *     15 = WBMP，16 = XBM
             */
            $type = getImageSize($filepath)[2]; 
            $allowedTypes = array(
                1,  //  gif
                2,  //  jpg
                3   //  png
            );

            if (!in_array($type, $allowedTypes)) {
                return false;
            }

            switch ($type) {
                case 1 :
                    $src_img = imageCreateFromGif($filepath);
                    break;
                case 2 :
                    $src_img = imageCreateFromJpeg($filepath);
                    break;
                case 3 :
                    $src_img = imageCreateFromPng($filepath);
                    break;
            }

            $dst_img = imagecreatetruecolor($thumb_w, $thumb_h);
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $orig_w, $orig_h);
            $resized = imagejpeg($dst_img, storage_path("app/images/thumbnail/{$realname}"), 90);

            return $resized;
        } else {
            return false;
        }
    }

    public function __get($key)
    {
        if ($this->getAttribute('extras') && property_exists($this->getAttribute('extras'), $key)) {
            return $this->getAttribute('extras')->$key;
        }

        return parent::__get($key);
    }

    public function __isset($key)
    {
        if ($this->getAttribute('extras') && property_exists($this->getAttribute('extras'), $key)) {
            return true;
        }

        return parent::__isset($key);
    }
}
