<?php

declare(strict_types=1);

namespace Darkheim\Application\Api;

final class GuildmarkApiController
{
    private const MIN_SIZE = 8;
    private const MAX_SIZE = 512;
    private const DEFAULT_SIZE = 40;

    public function render(): void
    {
        $size = self::DEFAULT_SIZE;
        if (isset($_GET['size']) && is_numeric($_GET['size']) && $_GET['size'] >= self::MIN_SIZE && $_GET['size'] <= self::MAX_SIZE) {
            $size = (int) $_GET['size'];
        }

        $binaryData = (string) ($_GET['data'] ?? '');
        if (strlen($binaryData) !== 64) {
            $binaryData = bin2hex($binaryData);
        }

        $pixelSize = $size / 8;
        $hex = $binaryData;
        $grid = [];

        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $offset = ($y * 8) + $x;
                $grid[$y][$x] = $hex[$offset] ?? '0';
            }
        }

        $scaledGrid = [];
        for ($y = 1; $y <= 8; $y++) {
            for ($x = 1; $x <= 8; $x++) {
                $bit = $grid[$y - 1][$x - 1] ?? '0';
                for ($repeatY = 0; $repeatY < $pixelSize; $repeatY++) {
                    for ($repeat = 0; $repeat < $pixelSize; $repeat++) {
                        $translatedY = (int) ((($y - 1) * $pixelSize) + $repeatY);
                        $translatedX = (int) ((($x - 1) * $pixelSize) + $repeat);
                        $scaledGrid[$translatedY][$translatedX] = $bit;
                    }
                }
            }
        }

        $img = imagecreate($size, $size);
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $bit = $scaledGrid[$y][$x] ?? '0';
                $color = substr($this->color((string) $bit), 1);
                $r = substr($color, 0, 2);
                $g = substr($color, 2, 2);
                $b = substr($color, 4, 2);
                $superPixel = imagecreate(1, 1);
                $cl = imagecolorallocatealpha($superPixel, hexdec($r), hexdec($g), hexdec($b), 0);
                imagefilledrectangle($superPixel, 0, 0, 1, 1, $cl);
                imagecopy($img, $superPixel, $x, $y, 0, 0, 1, 1);
                imagedestroy($superPixel);
            }
        }

        header('Content-type: image/gif');
        imagerectangle($img, 0, 0, $size - 1, $size - 1, imagecolorallocate($img, 0, 0, 0));
        imagecolortransparent($img, imagecolorexact($img, 17, 17, 17));
        imagegif($img);
        imagedestroy($img);
    }

    private function color(string $mark): string
    {
        return match (strtoupper($mark)) {
            '0' => '#111111',
            '1' => '#000000',
            '2' => '#808080',
            '3' => '#ffffff',
            '4' => '#fe0000',
            '5' => '#ff7f00',
            '6' => '#ffff00',
            '7' => '#80ff00',
            '8' => '#00ff01',
            '9' => '#00fe81',
            'A' => '#00ffff',
            'B' => '#0080ff',
            'C' => '#0000fe',
            'D' => '#7f00ff',
            'E' => '#ff00fe',
            'F' => '#ff0080',
            default => '#111111',
        };
    }
}

