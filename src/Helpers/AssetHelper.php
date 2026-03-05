<?php

namespace Mralston\Payment\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;

class AssetHelper
{
    public static function loadPackageAssets(string $packageName, string $entrypoint): HtmlString
    {
        // If we're in local environment and the package is being developed with Hot Module Replacement (HMR),
        // we want to use the package's own Vite dev server if it's running.
        // However, the user wants a standard manner where the application loads the compiled assets.
        // To support the best of both worlds, we'll check for a hot file in the package's build directory.
        $packageBuildPath = "vendor/{$packageName}/build";
        $hotPath = public_path("{$packageBuildPath}/hot");

        if (app()->environment('local') && File::exists($hotPath)) {
            $hmrUrl = File::get($hotPath);
            $jsUrl = "{$hmrUrl}/{$entrypoint}";

            return new HtmlString("<script type=\"module\" src=\"{$jsUrl}\"></script>");
        }

        $manifestPath = public_path("{$packageBuildPath}/manifest.json");

        if (! File::exists($manifestPath)) {
            return new HtmlString('');
        }

        $manifest = json_decode(File::get($manifestPath), true);

        if (! isset($manifest[$entrypoint])) {
            return new HtmlString('');
        }

        $asset = $manifest[$entrypoint];
        $html = '';

        // Add CSS links
        if (isset($asset['css'])) {
            foreach ($asset['css'] as $cssFile) {
                $url = asset("{$packageBuildPath}/{$cssFile}");
                $html .= "<link rel=\"stylesheet\" href=\"{$url}\">";
            }
        }

        // Add JS script
        $jsUrl = asset("{$packageBuildPath}/{$asset['file']}");
        $html .= "<script type=\"module\" src=\"{$jsUrl}\"></script>";

        return new HtmlString($html);
    }
}
