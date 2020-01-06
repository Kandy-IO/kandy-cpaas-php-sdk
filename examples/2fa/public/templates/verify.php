<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="templates/css/main.css" type="text/css">
  <title>2FA | Verify</title>
</head>
<body>
    <?php if (isset($alert)): ?>
        <div class="alert alert-error">
            <p><?php echo $alert ?></p>
        </div>
    <?php endif; ?>
    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success">
            <p><?php echo $success_msg ?></p>
        </div>
    <?php endif; ?>
    <div class="box centered-box">
        <form action="/verify" method="post">
            <h2 class="text-center">Verify</h2>
            <div class="input-group">
                <label for="code">Verification code</label>
                <input type="text" id="code" maxlength="6" name="code" />
                <button type="submit">Verify</button>
            </div>
        </form>
        <hr>
        <form action="/sendcode" method="post">
            <div class='field-set'>
                <input type="radio" name="tfa" value="sms" checked/> 2FA via sms
                <input type="radio" name="tfa" value="email" /> 2FA via email
                <button class='verify-button' type="submit">send 2FA</button>
            </div>
        </form>
    </div>
</body>
</html>
