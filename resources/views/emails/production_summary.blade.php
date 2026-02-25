<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registros de Producción</title>
  </head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; padding:32px 16px;">
    <tr>
      <td align="center">

        <table width="100%" cellpadding="0" cellspacing="0"
               style="max-width:800px; background-color:#ffffff; border-radius:16px; border:1px solid #e2e8f0; overflow:hidden;">

          <tr>
            <td style="padding:24px 40px; border-bottom:1px solid #e2e8f0; background-color:#ecfdf5; border-left:6px solid #10b981;">
              <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td style="vertical-align:middle; width:32px;">
                    <img src="https://cdn-icons-png.flaticon.com/512/190/190411.png" width="24" height="24" alt="check" style="display:block;">
                  </td>
                  <td style="vertical-align:middle; padding-left:12px;">
                    <h1 style="margin:0; font-size:18px; font-weight:800; color:#065f46; letter-spacing:-0.01em;">
                      ENVÍO COMPLETADO EXITOSAMENTE
                    </h1>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:32px 40px 16px;">
              <p style="margin:0 0 8px; font-size:15px; color:#1e293b; font-weight:600;">Estimados,</p>
              <br>
              <p style="margin:0; font-size:14px; color:#475569; line-height:1.6;">
                Les informamos que se ha completado el envío de los registros de producción correspondientes al sistema de pintura hacia <strong>Infor</strong>.
              </p>
              <br>
              <p style="margin:0; font-size:14px; color:#475569; line-height:1.6;">
                A continuación, se presenta un resumen de los registros que fueron enviados:
              </p>
            </td>
          </tr>

          <tr>
            <td style="padding:16px 32px 32px;">
              <div style="border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:12px;">
                  <thead>
                    <tr style="background-color:#f9fafb;">
                      <th style="padding:12px 16px; text-align:left; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0; text-transform:uppercase; font-size:11px;">No. de Orden</th>
                      <th style="padding:12px 16px; text-align:left; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0; text-transform:uppercase; font-size:11px;">Estación</th>
                      <th style="padding:12px 16px; text-align:left; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0; text-transform:uppercase; font-size:11px;">No. de Parte</th>
                      <th style="padding:12px 16px; text-align:center; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0; text-transform:uppercase; font-size:11px;">Turno</th>
                      <th style="padding:12px 16px; text-align:right; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0; text-transform:uppercase; font-size:11px;">Cant. Plan</th>
                      <th style="padding:12px 16px; text-align:right; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0; text-transform:uppercase; font-size:11px;">Cant. Real</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($records as $index => $row)
                    <tr style="background-color:{{ $index % 2 === 0 ? '#ffffff' : '#fcfcfd' }};">
                      <td style="padding:10px 16px; color:#1e293b; border-bottom:1px solid #f1f5f9; font-weight:600;">
                        {{ $row['order_number'] }}
                      </td>
                      <td style="padding:10px 16px; color:#475569; border-bottom:1px solid #f1f5f9;">
                        {{ $row['work_center'] }}
                      </td>
                      <td style="padding:10px 16px; color:#475569; border-bottom:1px solid #f1f5f9;">
                        {{ $row['part_number'] }}
                      </td>
                      <td style="padding:10px 16px; color:#475569; border-bottom:1px solid #f1f5f9; text-align:center;">
                        <span style="background-color:#f1f5f9; padding:3px 10px; border-radius:12px; border:1px solid #e2e8f0; font-size:11px; font-weight:700; color:#1e293b;">
                            {{ $row['shift'] }}
                        </span>
                      </td>
                      <td style="padding:10px 16px; border-bottom:1px solid #f1f5f9; text-align:right;">
                        <span style="background-color:#f1f5f9; padding:3px 10px; border-radius:12px; border:1px solid #e2e8f0; font-size:11px; font-weight:600; color:#1e293b;">
                          {{ number_format($row['planned_qty']) }}
                        </span>
                      </td>
                      <td style="padding:10px 16px; border-bottom:1px solid #f1f5f9; text-align:right;">
                        <span style="background-color:#f1f5f9; padding:3px 10px; border-radius:12px; border:1px solid #e2e8f0; font-size:11px; font-weight:700; color:#1e293b;">
                          {{ number_format($row['produced_qty']) }}
                        </span>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </td>
          </tr>

          <tr>
            <td style="padding:0 40px 32px;">
            <p style="margin:0; font-size:14px; color:#475569; line-height:1.6;">
                Quedamos atentos a cualquier duda o comentario que puedan tener.
              </p>
              <br>
              <p style="margin:0; font-size:14px; color:#475569;">
                Saludos cordiales,<br>
              </p>
            </td>
          </tr>

          <tr>
            <td style="background-color:#f8fafc; border-top:1px solid #e2e8f0; padding:24px 40px; text-align:center;">
              <p style="margin:0; font-size:12px; color:#94a3b8; line-height:1.5;">
                Este es un mensaje generado automáticamente, por favor no responder a este correo.
              </p>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
