<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $informativo->title }}</title>
</head>
<body style="margin:0; padding:0; background-color:#EBF1FF; font-family: Calibri, Candara, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#EBF1FF; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; max-width:600px; width:100%;">
                    <tr>
                        <td style="background-color:#D6E4FF; padding:24px 32px;" align="left">
                            <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="CETEM" height="40" style="display:block;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 8px; font-family: Arial, Helvetica, sans-serif; font-size:12px; letter-spacing:0.05em; text-transform:uppercase; color:#F4A000; font-weight:bold;">
                                {{ $informativo->sector->sigla ?? 'Comunicado geral' }}
                            </p>
                            <h1 style="margin:0 0 16px; font-family: Arial, Helvetica, sans-serif; font-size:22px; color:#333333;">
                                {{ $informativo->title }}
                            </h1>
                            <p style="margin:0 0 24px; font-size:13px; color:#666666;">
                                Publicado em {{ $informativo->published_at?->format('d/m/Y H:i') }}
                            </p>

                            @if($informativo->image)
                                <img src="{{ $message->embed(storage_path('app/public/' . $informativo->image)) }}" alt="" style="width:100%; border-radius:6px; margin-bottom:24px;">
                            @endif

                            <div style="font-size:15px; line-height:1.6; color:#333333; white-space:pre-line;">{{ $informativo->content }}</div>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:32px;">
                                <tr>
                                    <td style="background-color:#0052CC; border-radius:4px;">
                                        <a href="{{ route('informativos.show', $informativo) }}" style="display:inline-block; padding:12px 24px; color:#ffffff; text-decoration:none; font-family: Arial, Helvetica, sans-serif; font-size:14px; font-weight:bold;">
                                            Ver na intranet
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f5f5f5; padding:16px 32px; font-size:12px; color:#999999;" align="center">
                            Intranet CETEM — Centro de Tecnologia Mineral
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
