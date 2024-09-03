REQUIREMENTS
------------

The minimum requirements for this project:

- PHP 8.2
- Docker
- Composer

START PROJECT
------------
1. cp .env.example .env
2. composer install
3. ./vendor/bin/sail up -d
   alternatively you can use `alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'` and sail up -d
4. sail artisan key:generate
5. sail artisan storage:link
6. sail artisan migrate
