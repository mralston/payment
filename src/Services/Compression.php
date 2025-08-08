<?php

namespace Mralston\Payment\Services;

class Compression
{
    public function compress(mixed $data)
    {
        // JSON encode the data
        $jsonPayload = json_encode($data);

        // Compress the JSON string
        $compressedPayload = gzcompress($jsonPayload, 9);

        // Base64 encode the compressed data
        return base64_encode($compressedPayload);
    }
}
