<?php
declare(strict_types=1);

namespace App\Support;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;

final class QrCode
{
    public static function dataUri(string $data): string
    {
        $qrCode = new EndroidQrCode(
            data: $data,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 240,
            margin: 12,
            roundBlockSizeMode: RoundBlockSizeMode::None,
            foregroundColor: new Color(23, 35, 59),
        );

        return (new SvgWriter())->write($qrCode)->getDataUri();
    }
}
