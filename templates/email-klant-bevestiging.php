<!DOCTYPE html>
<html>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 0;">
    <tr><td align="center">
      <table width="520" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">

        <tr>
          <td style="background:#e8e8e8;padding:28px 32px;text-align:center;">
            <p style="margin:0;color:#777;font-size:13px;letter-spacing:1px;text-transform:uppercase;">Visual Booker</p>
            <h1 style="margin:6px 0 0;color:#333;font-size:22px;font-weight:700;">Boekingsbevestiging</h1>
          </td>
        </tr>

        <tr>
          <td style="padding:32px;">
            <p style="margin:0 0 24px;font-size:15px;color:#333;">Hoi <strong><?php echo esc_html( $customer_name ); ?></strong>,<br><br>Bedankt voor je boeking! Hieronder vind je een overzicht.</p>

            <p style="margin:0 0 8px;font-size:13px;color:#888;text-transform:uppercase;letter-spacing:1px;">Layout</p>
            <p style="margin:0 0 24px;font-size:16px;color:#222;font-weight:600;"><?php echo esc_html( $layout_title ); ?></p>

            <p style="margin:0 0 8px;font-size:13px;color:#888;text-transform:uppercase;letter-spacing:1px;">Geboekte spots</p>
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #f0f0f0;border-radius:6px;margin-bottom:24px;">
              <tr style="background:#f9f9f9;">
                <th style="padding:10px 12px;text-align:left;font-size:13px;color:#555;font-weight:600;">Spot</th>
                <th style="padding:10px 12px;text-align:right;font-size:13px;color:#555;font-weight:600;">Prijs</th>
              </tr>
              <?php echo $spots_rows_html; ?>
              <tr style="background:#f9f9f9;">
                <td style="padding:10px 12px;font-weight:700;color:#222;">Totaal</td>
                <td style="padding:10px 12px;text-align:right;font-weight:700;color:#333;font-size:16px;"><?php echo esc_html( $currency ) . number_format( $total, 2, ',', '.' ); ?></td>
              </tr>
            </table>

            <p style="margin:0 0 24px;font-size:14px;color:#555;">
              <strong>Status:</strong> ⏳ In afwachting
            </p>

            <p style="margin:0;font-size:14px;color:#888;">Je ontvangt een bevestiging zodra je boeking is goedgekeurd.</p>
          </td>
        </tr>

        <tr>
          <td style="padding:20px 32px;background:#f9f9f9;text-align:center;">
            <p style="margin:0;font-size:12px;color:#aaa;">Met vriendelijke groet, Visual Booker</p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
