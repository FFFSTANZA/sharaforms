<?php

namespace App\Service\Storage;

use App\Exceptions\UploadSecurityException;
use enshrined\svgSanitize\Sanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadSecurityService
{
    private const BLOCKED_EXTENSIONS = ['html', 'htm', 'xhtml', 'xml'];
    private const BLOCKED_MIME_TYPES = ['text/html', 'application/xhtml+xml', 'application/xml', 'text/xml'];

    /**
     * C2 FIX: Allowlist of permitted MIME types. Only safe file types are accepted.
     * Everything else is rejected, preventing upload of PHP, executables, etc.
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/avif',
        'image/svg+xml',
        'application/pdf',
        'text/plain',
        'text/csv',
        'application/json',
        'application/zip',
        'application/x-zip-compressed',
        'video/mp4',
        'video/webm',
        'audio/mpeg',
        'audio/wav',
        'audio/ogg',
        'font/ttf',
        'font/otf',
        'font/woff',
        'font/woff2',
        'application/vnd.ms-fontobject',
    ];

    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg',
        'pdf', 'txt', 'csv', 'json',
        'zip',
        'mp4', 'webm', 'mp3', 'wav', 'ogg',
        'ttf', 'otf', 'woff', 'woff2', 'eot',
    ];

    public function inspectUploadedFile(UploadedFile $file): UploadSecurityInspection
    {
        $contents = $file->get();

        if (!is_string($contents)) {
            throw new UploadSecurityException('Unable to read the uploaded file.');
        }

        return $this->inspectContents($contents, $file->getClientOriginalName());
    }

    public function inspectStoredFile(string $path, ?string $originalFileName = null): UploadSecurityInspection
    {
        if (!Storage::exists($path)) {
            throw new UploadSecurityException('File not found.');
        }

        return $this->inspectContents(Storage::get($path), $originalFileName);
    }

    private function inspectContents(string $contents, ?string $originalFileName = null): UploadSecurityInspection
    {
        $extension = strtolower((string) pathinfo($originalFileName ?? '', PATHINFO_EXTENSION));
        $mimeType = $this->detectMimeType($contents);

        if ($this->isBlockedActiveContent($extension, $mimeType)) {
            throw new UploadSecurityException('This file type is not allowed.');
        }

        if ($this->isSvg($extension, $mimeType)) {
            return new UploadSecurityInspection(
                mimeType: 'image/svg+xml',
                isImage: true,
                isSvg: true,
                sanitizedContents: $this->sanitizeSvg($contents),
            );
        }

        return new UploadSecurityInspection(
            mimeType: $mimeType,
            isImage: str_starts_with($mimeType, 'image/'),
            isSvg: false,
            sanitizedContents: null,
        );
    }

    private function detectMimeType(string $contents): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($contents);

        return is_string($mimeType) && $mimeType !== ''
            ? strtolower($mimeType)
            : 'application/octet-stream';
    }

    private function isBlockedActiveContent(string $extension, string $mimeType): bool
    {
        // C2 FIX: Use allowlist instead of blocklist. Reject anything not explicitly permitted.
        return !in_array($extension, self::ALLOWED_EXTENSIONS, true)
            || !in_array($mimeType, self::ALLOWED_MIME_TYPES, true);
    }

    private function isSvg(string $extension, string $mimeType): bool
    {
        return $extension === 'svg' || $mimeType === 'image/svg+xml';
    }

    private function sanitizeSvg(string $contents): string
    {
        $sanitizer = new Sanitizer();
        $sanitized = $sanitizer->sanitize($contents);

        if (!is_string($sanitized) || trim($sanitized) === '' || stripos($sanitized, '<svg') === false) {
            throw new UploadSecurityException('Invalid SVG file.');
        }

        return $sanitized;
    }
}
