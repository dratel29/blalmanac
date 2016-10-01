blalmanac
========================

## What is it?

![dahsboard](http://ocarina.fr/medias/blalmanac2.jpg)

## Installation

```sh
php -r "readfile('https://getcomposer.org/installer');" | php
php composer.phar update
php app/console assets:install web --symlink
php app/console doctrine:schema:create
php app/console server:start
open http://127.0.0.1:8000
```

## Usage

By accessing http://127.0.0.1:8000 you will see your rooms and their availability. If you enabled booking, you'll also be able to book free rooms from 30 mins to 3 hours later.

For easier management, at BlaBlaCar, rooms are named in the given format:

[`country code`] `floor`- `name` / `number of seats`

Example:

`[FR] -1 Bolshoi / 6p`

You can filter rooms directly in your URL, for example, if you only want rooms from France 3th floor, you can use:
`"http://127.0.0.1:8000/[FR] 3"` or `http://127.0.0.1:8000/[FR]%203`



## License

- This project is released under the MIT license

- Fuz logo is © 2013-2016 Alain Tiemblo

