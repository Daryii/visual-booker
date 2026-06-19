<!DOCTYPE html>
<html>
<body style="font-family:Arial,sans-serif;color:#333;">
  <table width="500" cellpadding="0" cellspacing="0">
    <tr>
      <td style="background:#e8e8e8;padding:20px;">
        <h2 style="margin:0;">Boeking goedgekeurd</h2>
      </td>
    </tr>

    <tr>
      <td style="padding:20px;">
        <p>Hoi <strong><?php echo esc_html( $customer_name ); ?></strong>,<br>
        Goed nieuws! Je boeking is goedgekeurd.</p>

        <p><strong>Locatie:</strong> <?php echo esc_html( $layout_title ); ?></p>
        <p><strong>Spot:</strong> <?php echo esc_html( $spot_label ); ?></p>
        <p><strong>Prijs:</strong> <?php echo esc_html( $currency ) . number_format( $price, 2, ',', '.' ); ?></p>
      </td>
    </tr>

  </table>
</body>
</html>
