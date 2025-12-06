<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageUploadService
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly string $uploadsDirectory
    ) {
    }

    public function upload(UploadedFile $file, ?string $prefix = null): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $uniqueId = uniqid('', true);

        $newFilename = $safeFilename . '-' . $uniqueId . '.' . $file->guessExtension();

        if ($prefix) {
            $newFilename = $prefix . '-' . $newFilename;
        }

        try {
            $file->move($this->uploadsDirectory, $newFilename);
        } catch (FileException $e) {
            throw new \RuntimeException('Failed to upload file: ' . $e->getMessage());
        }

        return $newFilename;
    }

    public function delete(string $filename): void
    {
        $filePath = $this->uploadsDirectory . '/' . $filename;

        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
        }
    }

    public function getUploadPath(string $filename): string
    {
        return '/uploads/products/' . $filename;
    }
}
