<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="templates/css/main.css" type="text/css">
  <title>2FA | Login</title>
</head>
<body>
    <?php if (isset($alert)): ?> 
        <div class="alert alert-error">
        <p><?php echo $alert ?></p>
        </div>
    <?php endif; ?>
    <form action="/login" method="post" class="box centered-box">
        <h2 class="text-center">Login</h2>
        <div class="input-group">
        <label for="email">Email</label>
        <input type="text" id="email" name="email" />
        </div>
        <div class="input-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" />
        </div>
        <button type="submit">Login</button>
    </form>
</body>
</html>
