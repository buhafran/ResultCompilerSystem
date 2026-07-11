<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

final class ResultQrCode
{
    public static function dataUri(string $value, int $size = 150): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );

        $svg = (new Writer($renderer))->writeString($value);
        $svg = preg_replace('/<\?xml.*?\?>/s', '', $svg) ?: $svg;

        return 'data:image/svg+xml;base64,'.base64_encode(trim($svg));
    }
}
