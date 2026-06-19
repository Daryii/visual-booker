<!DOCTYPE html>
<html>
<body style="font-family:Arial,sans-serif;color:#333;">
  <table width="500" cellpadding="0" cellspacing="0">
    <tr>
      <td style="background:#e8e8e8;padding:20px;">
        <h2 style="margin:0;">Boekingsbevestiging</h2>
      </td>
    </tr>

    <tr>
      <td style="padding:20px;">
        <p>Hoi <strong><?php echo esc_html( $customer_name ); ?></strong>,<br>
        Bedankt voor je boeking! Hieronder vind je een overzicht.</p>

        <p><strong>Locatie:</strong> <?php echo esc_html( $layout_title ); ?></p>

        <p><strong>Geboekte spots</strong></p>
        <table width="100%" cellpadding="6" cellspacing="0" border="1" style="border-collapse:collapse;">
          <tr>
            <th align="left">Spot</th>
            <th align="right">Prijs</th>
          </tr>
          <?php echo $spots_rows_html; ?>
          <tr>
            <td><strong>Totaal</strong></td>
            <td align="right"><strong><?php echo esc_html( $currency ) . number_format( $total, 2, ',', '.' ); ?></strong></td>
          </tr>
        </table>

        <p>Je ontvangt een bevestiging zodra je boeking is goedgekeurd.</p>
      </td>
    </tr>

  </table>
</body>
</html>
