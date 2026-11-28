<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title')</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f1;font-family:Arial,Helvetica,sans-serif;color:#1c261b;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;background:#f4f6f1;">
    <tr>
      <td align="center" style="padding:32px 12px;">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #dde3d8;">
          <tr>
            <td style="background:#17330f;padding:28px 40px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="font-size:24px;line-height:30px;font-weight:bold;color:#ffffff;letter-spacing:-0.4px;">
                    <span style="display:inline-block;width:34px;height:34px;margin-right:10px;background:#39a900;border-radius:10px;text-align:center;vertical-align:middle;font-size:20px;line-height:34px;">S</span>
                    StockMind
                  </td>
                  <td align="right" style="font-size:11px;line-height:16px;color:#dfeadc;text-transform:uppercase;letter-spacing:1.2px;">@yield('category')</td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="height:5px;background:#39a900;font-size:0;line-height:0;">&nbsp;</td>
          </tr>
          <tr>
            <td style="padding:40px;">
              @yield('content')
            </td>
          </tr>
          <tr>
            <td style="padding:22px 40px;background:#f4f6f1;border-top:1px solid #dde3d8;">
              <p style="margin:0 0 6px;font-size:13px;line-height:20px;color:#5f6b5c;text-align:center;">
                Este mensaje fue enviado por <strong style="color:#17330f;">StockMind</strong>.
              </p>
              <p style="margin:0;font-size:12px;line-height:18px;color:#7a8477;text-align:center;">
                Si no esperabas este correo, puedes ignorarlo o comunicarte con el administrador.
              </p>
            </td>
          </tr>
        </table>
        <p style="margin:16px 0 0;font-size:11px;line-height:16px;color:#8a9386;text-align:center;">
          &copy; {{ date('Y') }} StockMind
        </p>
      </td>
    </tr>
  </table>
</body>
</html>
