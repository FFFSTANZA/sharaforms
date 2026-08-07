<?php

namespace App\Http\Controllers\Content;

use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Vapor\Http\Controllers\SignedStorageUrlController as Controller;

class SignedStorageUrlController extends Controller
{
    /**
     * Create a new signed URL.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->ensureEnvironmentVariablesAreAvailable($request);
        $bucket = $request->input('bucket') ?: config('filesystems.disks.r2.bucket');

        $client = $this->storageClient();

        $uuid = (string) Str::uuid();

        $expiresAfter = config('vapor.signed_storage_url_expires_after', 5);

        $signedRequest = $client->createPresignedRequest(
            $this->createCommand($request, $client, $bucket, $key = ('tmp/'.$uuid)),
            sprintf('+%s minutes', $expiresAfter)
        );

        $uri = $signedRequest->getUri();

        return response()->json([
            'uuid' => $uuid,
            'bucket' => $bucket,
            'key' => $key,
            'url' => $uri->getScheme().'://'.$uri->getAuthority().$uri->getPath().'?'.$uri->getQuery(),
            'headers' => $this->headers($request, $signedRequest),
        ], 201);
    }

    /**
     * Ensure the required environment variables are available.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function ensureEnvironmentVariablesAreAvailable(Request $request)
    {
        $missing = array_diff_key(array_flip(array_filter([
            $request->input('bucket') ? null : 'R2_BUCKET',
            'R2_ACCESS_KEY_ID',
            'R2_SECRET_ACCESS_KEY',
        ])), $_ENV);

        if (empty($missing)) {
            return;
        }

        throw new InvalidArgumentException(
            'Unable to issue signed URL. Missing environment variables: '.implode(', ', array_keys($missing))
        );
    }

    /**
     * Get the S3-compatible (Cloudflare R2) storage client instance.
     *
     * @return \Aws\S3\S3Client
     */
    protected function storageClient()
    {
        $disk = config('filesystems.disks.r2');

        $config = [
            'region' => $disk['region'] ?? 'auto',
            'version' => 'latest',
            'signature_version' => 'v4',
            'use_path_style_endpoint' => $disk['use_path_style_endpoint'] ?? true,
        ];

        $config['credentials'] = array_filter([
            'key' => $disk['key'] ?? null,
            'secret' => $disk['secret'] ?? null,
        ]);

        if (! empty($disk['url'])) {
            $config['url'] = $disk['url'];
            $config['endpoint'] = $disk['url'];
        }

        if (! empty($disk['endpoint'])) {
            $config['endpoint'] = $disk['endpoint'];
        }

        return new S3Client($config);
    }
}
