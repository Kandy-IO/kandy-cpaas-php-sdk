# Get Started

In this quickstart, we will help you dip your toes in before you dive in. This guide will help you get started with the $KANDY$ PHP SDK.

## Using the SDK

To begin, you will need to make sure composer is installed and setup for your project.

and then execute the below command in your projects root directory.

```php
composer require cpaassdk/cpaassdk
```

In your application, you need to import cpaassdk `use cpaassdk/Client;`

```php
# Call the configure method with the required credentials.
$client = new Client(args);
```

After you've configured the SDK client, you can begin playing around with it to learn its functionality and see how it fits in your application. The API reference documentation will help to explain the details of the available features.

## Configuration

```php
$client = new Client(
  '<private project key>',
  '<private project secret>',
  'https://$KANDYFQDN$'
);
```

The information required to be authenticated should be under:

+ `Projects` -> `{your project}` -> `Project info`/`Project secret`

> + `Private Project key` should be mapped to `client_id`
> + `Private Project secret` should be mapped to `client_secret`

## Usage

All modules can be accessed via the client instance. All method invocations follow the namespaced signature

`{client}->{module_name}->{method_name}(params)`

Example:

```php
$client->twofactor->send_code($params);
```

## Default Error Response

### Format

```php
{
  name: '<exception type>',
  exception_id: '<exception id/code>',
  message: '<exception message>'
}
```

### Example

```php
{
  name: 'serviceException',
  exception_id: 'SVC0002',
  message: 'Invalid input value for message part address'
}
```
