<?php
/** @var string $name @var string $code @var string $link @var bool $test */
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$displayName = function_exists('mb_strtoupper') ? mb_strtoupper($name, 'UTF-8') : strtoupper($name);
?>
<!doctype html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="color-scheme" content="light"><title>NFA Farmer Seller Registry: Password Reset Request</title>
<!--[if mso]><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
<style>@media only screen and (max-width:600px){.email-shell{width:100%!important}.email-copy{padding-left:20px!important;padding-right:20px!important}.email-title{font-size:18px!important;letter-spacing:3px!important}.email-greeting{font-size:20px!important}}</style>
</head>
<body style="margin:0;padding:0;background-color:#ffffff;color:#111111;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;background-color:#ffffff;"><tr><td align="center" style="padding:16px 10px;">
<!--[if mso]><table role="presentation" width="720" align="center"><tr><td><![endif]-->
<table role="presentation" class="email-shell" align="center" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:720px;table-layout:fixed;margin:0 auto;border:4px solid #000000;background-color:#ffffff;">
<tr><td style="padding:20px 12px 14px;"><img src="cid:fsr-reset-header" alt="National Food Authority — FARMER SELLER REGISTRY" width="688" border="0" style="display:block;width:100%;max-width:688px;height:auto;"></td></tr>
<tr><td align="center" style="padding:6px 20px 30px;"><table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td class="email-title" align="center" bgcolor="#ff5259" style="padding:11px 14px;background-color:#ff5259;color:#000000;font-family:Arial,Helvetica,sans-serif;font-size:24px;font-weight:700;letter-spacing:5px;line-height:1.3;">PASSWORD RESET REQUEST</td></tr></table></td></tr>
<tr><td class="email-copy" style="padding:0 32px;font-family:Arial,Helvetica,sans-serif;font-size:16px;line-height:1.4;text-align:left;color:#111111;">
<h1 class="email-greeting" style="margin:0 0 28px;font-size:24px;line-height:1.25;font-weight:700;">HI, <?= $escape($displayName) ?></h1>
<p style="margin:0 0 22px;">Your password reset confirmation code is: <strong style="font-weight:400;white-space:nowrap;"><?= $escape($code) ?></strong></p>
<p style="margin:0 0 34px;">Open this link and enter your code:<br><a href="<?= $escape($link) ?>" style="color:#064b3e;text-decoration:underline;overflow-wrap:anywhere;word-break:break-all;"><?= $escape($link) ?></a></p>
<p style="margin:0 0 20px;">The code and link expire in 15 minutes. You can also enter the code with your employee number on the password reset screen.</p>
</td></tr>
<tr><td style="padding:0 8px 5px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td background="cid:fsr-reset-footer" bgcolor="#ffffff" height="290" valign="top" style="height:290px;background-color:#ffffff;background-image:url('cid:fsr-reset-footer');background-size:100% auto;background-repeat:no-repeat;background-position:center bottom;">
<!--[if gte mso 9]><v:rect fill="true" stroke="false" style="width:522pt;height:217.5pt;"><v:fill type="frame" src="cid:fsr-reset-footer" color="#f5fbff"/><v:textbox inset="0,0,0,0"><![endif]-->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td class="email-copy" style="padding:4px 24px 0;font-family:Arial,Helvetica,sans-serif;font-size:16px;line-height:1.4;text-align:left;color:#111111;">
<p style="margin:0 0 22px;">If you did not request this, ignore this email. Your password has not changed.</p>
<p style="margin:0;">Yours,<br>FSR System Administrator</p>
</td></tr></table>
<!--[if gte mso 9]></v:textbox></v:rect><![endif]-->
</td></tr></table>
</td></tr>
</table>
<!--[if mso]></td></tr></table><![endif]-->
<?php if ($test): ?><p style="max-width:720px;margin:12px auto 0;color:#666666;font-size:12px;line-height:1.4;">Delivery test only. This sample code and link cannot reset an account.</p><?php endif; ?>
</td></tr></table>
</body></html>
