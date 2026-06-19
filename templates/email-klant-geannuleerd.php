<!DOCTYPE html>
<html>
<body style="font-family:Arial,sans-serif;color:#333;">
  <table width="500" cellpadding="0" cellspacing="0">
    <tr>
      <td style="background:#e8e8e8;padding:20px;">
        <h2 style="margin:0;">Boeking geannuleerd</h2>
      </td>
    </tr>

    <tr>
      <td style="padding:20px;">
        <p>Hoi <strong><?php echo esc_html( $customer_name ); ?></strong>,<br>
        Helaas, je boeking is geannuleerd.</p>

        <p><strong>Locatie:</strong> <?php echo esc_html( $layout_title ); ?></p>
        <p><strong>Spot:</strong> <?php echo esc_html( $spot_label ); ?></p>

        <p>Heb je vragen? Neem gerust contact met ons op.</p>
      </td>
    </tr>

  </table>
</body>
</html>
