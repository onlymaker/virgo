# Install #
```php
composer install --no-dev --prefer-dist --optimize-autoloader
```
Or, install from docker
```php
docker run --rm --name php -d -p 80:80 -v $PWD:/var/www syncxplus/php
docker exec -it php composer install -d /var/www  --no-dev --prefer-dist --optimize-autoloader
```
Install font
```php
curl -o ./html/SourceHanSansSC-Normal.otf -L https://github.com/adobe-fonts/source-han-sans/raw/release/OTF/SimplifiedChinese/SourceHanSansSC-Normal.otf
```
# Test #
```php
composer install --prefer-dist --optimize-autoloader
./vendor/bin/phpunit -c phpunit.xml test
./vendor/bin/phpunit test/db
./vendor/bin/phpunit test/db/MysqlTest
./vendor/bin/phpunit test/db/MysqlTest.php
```
