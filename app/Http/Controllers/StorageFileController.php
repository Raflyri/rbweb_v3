<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serve files from the "public" disk over HTTP.
 *
 * Normally public/storage is a symlink to storage/app/public and the web
 * server handles this without PHP. That is impossible on this host: both
 * symlink() and exec() are disabled, so Filesystem::link() has no code path
 * left — `storage:link` fails with "Call to undefined function exec()" no
 * matter how it is invoked. Uploaded thumbnails were stored correctly but
 * every URL 404'd.
 *
 * Streaming them through a route removes the dependency on the filesystem
 * entirely. The cost is that images pass through PHP, which is acceptable
 * here: the site sits behind Cloudflare, and the long immutable cache header
 * below means the edge answers everything after the first request.
 */
class StorageFileController extends Controller
{
    /** Uploaded filenames are unique per upload, so responses never change. */
    protected const CACHE_SECONDS = 31536000; // 1 year

    public function show(Request $request, string $path): Response
    {
        $disk = Storage::disk('public');

        if (! $this->isSafePath($path) || ! $disk->exists($path)) {
            abort(404);
        }

        // Directories exist() as false on the local driver, but guard anyway
        // so a stray request can never stream something unreadable.
        if (! is_file($disk->path($path))) {
            abort(404);
        }

        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=' . static::CACHE_SECONDS . ', immutable',
            'Last-Modified' => gmdate('D, d M Y H:i:s', $disk->lastModified($path)) . ' GMT',
        ]);
    }

    /**
     * Reject anything that tries to climb out of the disk root.
     *
     * The local driver already refuses to resolve outside its root, but a
     * path check that is obvious beats one that depends on a library's
     * internals staying strict.
     */
    protected function isSafePath(string $path): bool
    {
        if ($path === '' || str_contains($path, "\0")) {
            return false;
        }

        foreach (explode('/', str_replace('\\', '/', $path)) as $segment) {
            if ($segment === '..') {
                return false;
            }
        }

        return true;
    }
}
