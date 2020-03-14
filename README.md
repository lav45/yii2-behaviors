# yii2-behaviors

[![Latest Stable Version](https://poser.pugx.org/lav45/yii2-behaviors/v/stable)](https://packagist.org/packages/lav45/yii2-behaviors)
[![License](https://poser.pugx.org/lav45/yii2-behaviors/license)](https://packagist.org/packages/lav45/yii2-behaviors)
[![Total Downloads](https://poser.pugx.org/lav45/yii2-behaviors/downloads)](https://packagist.org/packages/lav45/yii2-behaviors)
[![Build Status](https://travis-ci.org/lav45/yii2-behaviors.svg?branch=master)](https://travis-ci.org/lav45/yii2-behaviors)

This is a set of small Yii 3.0 behaviors extensions.

## Installation

The preferred way to install this extension through [composer](http://getcomposer.org/download/).

You can set the console

```
~$ composer require lav45/yii2-behaviors
```

or add

```
"lav45/yii2-behaviors": "^1.0"
```

in ```require``` section in `composer.json` file.


## Contents

- [SerializeBehavior](docs/SerializeBehavior.md)
- [SerializeProxyBehavior](docs/SerializeProxyBehavior.md)
- [PushBehavior](docs/PushBehavior.md)
- [PushModelBehavior](docs/PushModelBehavior.md)
- [CorrectDateBehavior](docs/CorrectDateBehavior.md)


# Testing

```
~$ docker build --pull --rm -t php74-test .
~$ ./container composer update --prefer-dist
~$ ./container vendor/bin/phpunit
```

## License

**yii2-behaviors** it is available under a BSD 3-Clause License. Detailed information can be found in the `LICENSE.md`.
