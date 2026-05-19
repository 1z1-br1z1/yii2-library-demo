<?php

namespace app\services;

use RuntimeException;
use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class UploadedFileService {
    /**
     * @var string
     */
    private string $uploadDir;

    /**
     * @param string $uploadDir
     */
    public function __construct(string $uploadDir) {
        $this->uploadDir = $uploadDir;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return string
     */
    public function save(UploadedFile $uploadedFile): string {
        $webRoot = Yii::getAlias('@webroot');
        $hash = hash('fnv1a64', $uploadedFile->tempName);
        $path = Yii::getAlias('@uploads'). substr($hash, 0, 2). '/'. substr($hash, 2, 2). '/'. substr($hash, 4, 2). '/'. substr($hash, 6);

        $this->checkDirectory($path);

        if (is_null($mime = FileHelper::getMimeType($uploadedFile->tempName, null, false))) {
            $mime = 'application/octet-stream';
        }

        if (is_null($ext = FileHelper::getExtensionByMimeType($mime, true))) {
            $ext = 'bin';
        }

        $path .= '.'. $ext;

        if (empty($uploadedFile->saveAs($path))) {
            throw new RuntimeException("Can't save uploaded file.");
        }

        return substr($path, strlen($webRoot));
    }

    public function checkDirectory(string $directory): bool {
        if (!is_dir($directory) && !mkdir($directory, 0775, true)) {
            throw new RuntimeException("Cannot create directory {$directory}.");
        }

        return true;
    }
}
