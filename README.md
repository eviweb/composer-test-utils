# Composer Test Utils

Set of utilities to help testing Composer related components.

## Installation

Run `composer require eviweb/composer-test-utils`.

## Usage

### ComposerRunner

The `ComposerRunner` class offers a convenient way to run the composer command and get the related output.

```php
use Eviweb\Composer\Testing\ComposerRunner;

$path = '/some/path';
$composer = new ComposerRunner();
$output = $composer->setWorkingDirectory($path)
    ->run('require', '--dev', 'vendor/package');
if ($composer->succeed()) {
    print('It succeed and return the following output: '.$output);
} else {
    print('It failed with the following error output: '.$output);
    print('The value of $composer->failed() is true.');
}
```

## License

This project is licensed under the terms of the [MIT License](LICENSE.md).
