@extends('emails.layout')

@section('title', 'Registro recibido')
@section('category', 'Registro')

@section('content')
  <div style="width:48px;height:48px;margin-bottom:20px;background:#eaf4e2;border-radius:14px;text-align:center;font-size:24px;line-height:48px;color:#2b8000;">
    &#10003;
  </div>

  <h1 style="margin:0 0 10px;font-size:26px;line-height:34px;color:#17330f;">Registro recibido</h1>
  <p style="margin:0 0 24px;font-size:16px;line-height:26px;color:#5f6b5c;">
    Hola <strong style="color:#1c261b;">{{ $usuario->nombre_completo }}</strong>,
  </p>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;background:#f8faf6;border:1px solid #dde3d8;border-radius:12px;">
    <tr>
      <td style="padding:22px;">
        <p style="margin:0 0 12px;font-size:15px;line-height:24px;color:#1c261b;">
          Tu registro en <strong>StockMind</strong> fue recibido correctamente.
        </p>
        <p style="margin:0;font-size:15px;line-height:24px;color:#5f6b5c;">
          Tu cuenta está pendiente hasta que un administrador asigne tus roles y permisos. Te enviaremos otro correo cuando tu acceso sea aprobado.
        </p>
      </td>
    </tr>
  </table>

  <p style="margin:24px 0 0;font-size:13px;line-height:20px;color:#7a8477;">
    Conserva este correo como confirmación de tu solicitud de acceso.
  </p>
@endsection
