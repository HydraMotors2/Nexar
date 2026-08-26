<?php
/**
 * NEXAR - File Upload Handler
 * Handles image uploads with compression and validation
 */

defined('NEXAR_APP') or define('NEXAR_APP', true);

class UploadHandler {
    private array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private array $maxSizes = [
        'logo' => 2 * 1024 * 1024,      // 2MB
        'banner' => 5 * 1024 * 1024,    // 5MB
        'gallery' => 3 * 1024 * 1024,   // 3MB
        'portfolio' => 5 * 1024 * 1024, // 5MB
        'avatar' => 2 * 1024 * 1024,    // 2MB
    ];
    
    private array $dimensions = [
        'logo' => ['width' => 400, 'height' => 400],
        'banner' => ['width' => 1920, 'height' => 480],
        'gallery' => ['width' => 1200, 'height' => 800],
        'portfolio' => ['width' => 1200, 'height' => 800],
        'avatar' => ['width' => 300, 'height' => 300],
    ];
    
    private string $uploadPath;
    private string $userId;
    
    public function __construct(string $userId = '') {
        $this->uploadPath = UPLOAD_PATH . '/users/' . $userId;
        $this->userId = $userId;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    /**
     * Handle file upload
     */
    public function upload(string $inputName, string $type = 'gallery'): array {
        if (!isset($_FILES[$inputName])) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }
        
        $file = $_FILES[$inputName];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => $this->getUploadErrorMessage($file['error'])];
        }
        
        // Validate file type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type. Allowed: JPEG, PNG, GIF, WebP'];
        }
        
        // Validate file size
        $maxSize = $this->maxSizes[$type] ?? $this->maxSizes['gallery'];
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'File too large. Maximum: ' . $this->formatBytes($maxSize)];
        }
        
        // Generate unique filename
        $extension = $this->getExtension($mimeType);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $this->uploadPath . '/' . $filename;
        
        // Compress and save image
        $result = $this->compressAndSave($file['tmp_name'], $filepath, $type);
        
        if (!$result['success']) {
            return $result;
        }
        
        // Generate thumbnail
        $thumbnailPath = $this->generateThumbnail($filepath, $type);
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => '/uploads/users/' . $this->userId . '/' . $filename,
            'thumbnail' => $thumbnailPath ? '/uploads/users/' . $this->userId . '/' . basename($thumbnailPath) : null,
            'size' => filesize($filepath),
            'mime_type' => $mimeType,
            'dimensions' => getimagesize($filepath),
        ];
    }
    
    /**
     * Compress and save image
     */
    private function compressAndSave(string $source, string $destination, string $type): array {
        $dims = $this->dimensions[$type] ?? ['width' => 1200, 'height' => 800];
        
        // Load image
        $image = $this->loadImage($source);
        if (!$image) {
            return ['success' => false, 'error' => 'Failed to process image'];
        }
        
        // Resize image
        $resized = $this->resizeImage($image['resource'], $image['width'], $image['height'], $dims['width'], $dims['height']);
        
        // Save with compression
        $quality = 85; // Good balance of quality and size
        $extension = pathinfo($destination, PATHINFO_EXTENSION);
        
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($resized, $destination, $quality);
                break;
            case 'png':
                imagesavealpha($resized, true);
                imagepng($resized, $destination, 9);
                break;
            case 'webp':
                imagewebp($resized, $destination, $quality);
                break;
            default:
                imagejpeg($resized, $destination, $quality);
        }
        
        imagedestroy($image['resource']);
        imagedestroy($resized);
        
        return ['success' => true];
    }
    
    /**
     * Load image resource
     */
    private function loadImage(string $path): ?array {
        $imageInfo = getimagesize($path);
        if (!$imageInfo) return null;
        
        $mimeType = $imageInfo['mime'];
        $resource = null;
        
        switch ($mimeType) {
            case 'image/jpeg':
                $resource = imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $resource = imagecreatefrompng($path);
                break;
            case 'image/gif':
                $resource = imagecreatefromgif($path);
                break;
            case 'image/webp':
                $resource = imagecreatefromwebp($path);
                break;
        }
        
        return $resource ? [
            'resource' => $resource,
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
        ] : null;
    }
    
    /**
     * Resize image maintaining aspect ratio
     */
    private function resizeImage($source, int $srcWidth, int $srcHeight, int $maxWidth, int $maxHeight) {
        $ratio = min($maxWidth / $srcWidth, $maxHeight / $srcHeight);
        
        $newWidth = (int)($srcWidth * $ratio);
        $newHeight = (int)($srcHeight * $ratio);
        
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and WebP
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);
        
        return $resized;
    }
    
    /**
     * Generate thumbnail
     */
    private function generateThumbnail(string $filepath, string $type): ?string {
        $thumbSize = 300;
        $thumbPath = pathinfo($filepath, PATHINFO_DIRNAME) . '/thumb_' . pathinfo($filepath, PATHINFO_BASENAME);
        
        $image = $this->loadImage($filepath);
        if (!$image) return null;
        
        $resized = $this->resizeImage($image['resource'], $image['width'], $image['height'], $thumbSize, $thumbSize);
        
        // Create square thumbnail with center crop
        $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        
        $srcX = max(0, (imagesx($resized) - $thumbSize) / 2);
        $srcY = max(0, (imagesy($resized) - $thumbSize) / 2);
        
        imagecopy($thumb, $resized, 0, 0, $srcX, $srcY, $thumbSize, $thumbSize);
        
        imagejpeg($thumb, $thumbPath, 80);
        
        imagedestroy($resized);
        imagedestroy($thumb);
        
        return $thumbPath;
    }
    
    /**
     * Delete file
     */
    public function delete(string $filename): bool {
        $filepath = $this->uploadPath . '/' . $filename;
        $thumbPath = $this->uploadPath . '/thumb_' . $filename;
        
        $success = true;
        
        if (file_exists($filepath)) {
            $success = unlink($filepath) && $success;
        }
        
        if (file_exists($thumbPath)) {
            $success = unlink($thumbPath) && $success;
        }
        
        return $success;
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage(int $errorCode): string {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
        ];
        
        return $messages[$errorCode] ?? 'Unknown upload error';
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Get file extension from MIME type
     */
    private function getExtension(string $mimeType): string {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        
        return $map[$mimeType] ?? 'jpg';
    }
}

// Helper function
function upload(string $inputName, string $type = 'gallery'): array {
    $userId = auth()->id() ?? 'anonymous';
    $handler = new UploadHandler($userId);
    return $handler->upload($inputName, $type);
}