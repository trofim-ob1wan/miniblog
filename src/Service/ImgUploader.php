<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;


class ImgUploader
{
    private $pathDir;

    public function __construct($pathDir)
    {
        $this->pathDir = $pathDir;
    }

    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        $file->move($this->getPathDir(), $fileName);

        return $fileName;
    }

    public function getPathDir()
    {
        return $this->pathDir;
    }
}