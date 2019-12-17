<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" type="text/css" href="{{ url_for('static',    filename='css/main.css') }}">
  <title>2FA | Verify</title>
</head>
<body>
  {% if alert %}
    <div class="alert alert-error">
      <p>{{alert}}</p>
    </div>
  {% endif %}
  <form action="/verify" method="post" class="box centered-box">
    <h2 class="text-center">Verify</h2>
    <div class="input-group">
      <label for="code">Verification code</label>
      <input type="text" id="code" maxlength="6" name="code" />
      <button type="submit">Verify</button>
    </div>
  </form>
</body>
</html>
