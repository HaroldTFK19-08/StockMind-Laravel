@extends('emails.layout')

@section('title', 'Acceso aprobado')
@section('category', 'Acceso aprobado')

@section('content')
  <div style="width:48px;height:48px;margin-bottom:20px;background:#eaf4e2;border-radius:14px;text-align:center;font-size:24px;line-height:48px;color:#2b8000;">
    &#10003;
  </div>

  <h1 style="margin:0 0 10px;font-size:26px;line-height:34px;color:#17330f;">Tu acceso fue aprobado</h1>
  <p style="margin:0 0 24px;font-size:16px;line-height:26px;color:#5f6b5c;">
    Hola <strong style="color:#1c261b;">{{ $usuario->nombre_completo }}</strong>,
  </p>

  <p style="margin:0 0 18px;font-size:15px;line-height:24px;color:#1c261b;">
    Un administrador te asignó acceso a <strong>StockMind</strong>.
  </p>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;margin:0 0 18px;background:#f8faf6;border:1px solid #dde3d8;border-radius:12px;">
    <tr>
      <td style="padding:18px 20px;">
        <p style="margin:0 0 4px;font-size:12px;line-height:18px;color:#5f6b5c;text-transform:uppercase;letter-spacing:0.8px;">Roles asignados</p>
        <p style="margin:0;font-size:15px;line-height:23px;font-weight:bold;color:#17330f;">{{ implode(', ', $roles) }}</p>
      </td>
    </tr>
  </table>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;margin:0 0 22px;background:#17330f;border-radius:12px;">
    <tr>
      <td align="center" style="padding:22px 20px;">
        <p style="margin:0 0 8px;font-size:12px;line-height:18px;color:#dfeadc;text-transform:uppercase;letter-spacing:1px;">Código de activación</p>
        <p style="margin:0;font-size:30px;line-height:38px;font-weight:bold;letter-spacing:5px;color:#ffffff;">{{ $codigo }}</p>
      </td>
    </tr>
  </table>

  <p style="margin:0 0 22px;font-size:15px;line-height:24px;color:#5f6b5c;">
    Guarda este código de forma segura. Ya puedes ingresar al aplicativo con las credenciales que registraste.
  </p>

  <table role="presentation" cellpadding="0" cellspacing="0" border="0">
    <tr>
      <td style="border-radius:8px;background:#39a900;">
        <a href="{{ url('/') }}" style="display:inline-block;padding:13px 22px;font-size:14px;line-height:18px;font-weight:bold;color:#ffffff;text-decoration:none;">Ingresar a StockMind</a>
      </td>
    </tr>
  </table>
@endsection
