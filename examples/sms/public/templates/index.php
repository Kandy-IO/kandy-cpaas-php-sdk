<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>SMS | Home</title>

  <link rel="stylesheet" type="text/css" href="templates/css/main.css">
  <script src="templates/js/notification.js"></script>
</head>
<body>
  <?php if (isset($alert)): ?>
    <div class="alert alert-error">
      <p><?php echo $message ?></p>
    </div>
  <?php endif; ?>
  <?php if (isset($success)): ?>
    <div class="alert alert-success">
      <p><?php echo $message ?></p>
    </div>
  <?php endif; ?>
  <div class="container">
    <div class="box solid-background vertically-center">
      <h2 class="text-center">Send SMS</h2>
      <form action="/" method="post">
        <div class="input-group">
          <label for="number">Phone number (E164 format)</label>
          <input type="text" name="number" id="number" placeholder="+12223334444"/>
        </div>
        <div class="input-group">
          <label>Message</label>
          <input type="text" name="message" id="message" />
        </div>
        <button type="submit">Send</button>
      </form>
    </div>
    <div class="box">
      <form action="/subscribe" method="post">
        <div class="input-group">
          <label for="webhook">Webhook host URL(Ref. README for details)</label>
          <input type="text" name="webhook" id="webhook" />
        </div>
        <button type="submit">Subscribe</button>
      </form>
      <h2 class="text-center">SMS Notification</h2>
      <div id="notification" class="notification-box"></div>
    </div>
  </div>
  <script>
    if (window.smsNotification) {
      setInterval(window.smsNotification.render, 5000)
    }
  </script>
</body>
</html>