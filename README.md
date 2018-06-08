# [Bedrock](https://roots.io/bedrock/)

Bedrock - это современный WordPress-стэк, помогающий вам использовать лучшие средства разработки и удобную структуру проекта.

Большая часть идей Bedrock вдохновлена методологией [Двенадцатифакторного приложения](https://12factor.net/ru/), включая её [адаптированную версию для WordPress](https://roots.io/twelve-factor-wordpress/).

Данный репозиторий отличается от оригинала русифицированным описанием и комментариями, а так же изменённый корневой каталог веб-сервера (public_html вместо web), а так же предустановленным минимальным набором плагинов, которые используются практически в любом проекте.

## Преимущества

* Улучшенная структура проекта
* Управление зависимостями с помощью [Composer](http://getcomposer.org)
* Простая конфигурация WordPress с помощью env-файлов
* Переменные среды, определяемые [Dotenv](https://github.com/vlucas/phpdotenv)
* Автозагрузка обязательных (must-use) плагинов (можно использовать обычные плагины как must-used)
* Улучшенная безопасность (отдельный корневой каталог для веб-сервера и безопасное хэширование паролей с [wp-password-bcrypt](https://github.com/roots/wp-password-bcrypt))

Для дополнительных возможностей используйте [Trellis](https://github.com/roots/trellis):

* Разворачивание рабочей среды с [Vagrant](http://www.vagrantup.com/)
* Простое разворачивание на сервере с [Ansible](http://www.ansible.com/) (Ubuntu 16.04, PHP 7.1, MariaDB)
* Выгрузка на сервер одной командой

Полностью рабочий пример: [roots-example-project.com](https://github.com/roots/roots-example-project.com).

## Требования

* PHP >= 5.6
* Composer - [Установить](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)

## Установка

1. Создать проект:
	* при использовании оригинального пакета Composer: 

  		`composer create-project roots/bedrock your-project-folder-name`
  
	* при использовании данного репозитория:

		`git clone github.com/iron-cherep/bedrock-boilerplate your-project-folder-name` 

2. Установить переменные среды в файле `.env`:
  * `DB_NAME` - База данных
  * `DB_USER` - Пользователь базы данных
  * `DB_PASSWORD` - Пароль базы данных
  * `DB_HOST` - Хост базы данных
  * `WP_ENV` - Среда окружения (`development`, `staging`, `production`)
  * `WP_HOME` - Полный URL домашней страницы WordPress (http://example.com)
  * `WP_SITEURL` - Полный URL к директории WordPress (http://example.com/wp)
  * `AUTH_KEY`, `SECURE_AUTH_KEY`, `LOGGED_IN_KEY`, `NONCE_KEY`, `AUTH_SALT`, `SECURE_AUTH_SALT`, `LOGGED_IN_SALT`, `NONCE_SALT`

	  Вы можете автоматически сгенерировать ключи безопасности (при условии, что у вас уже установлен wp-cli), использовав удобную команду [wp-cli-dotenv-command][wp-cli-dotenv]:
	
		  wp package install aaemnnosttv/wp-cli-dotenv-command
	
		  wp dotenv salts regenerate
	
	  Так же ключи можно создать в генераторе [Roots WordPress Salt Generator][roots-wp-salt].

3. Добавить тему в `public_html/app/themes` как и в случае с обычной установкой WordPress.

4. Установить корневой каталог для веб-сервера в папку `/path/to/site/public_html/` (`/path/to/site/current/public_html/` для автоматизированного процесса разворачивания)

5. Зайдите в панель администратора `http://example.com/wp/wp-admin`

## Разворачивание на сервере

Из коробки есть два метода разворачивания Bedrock на сервере:

* [Trellis](https://github.com/roots/trellis)
* [bedrock-capistrano](https://github.com/roots/bedrock-capistrano)

Может использоваться любой метод, единственное требование - выполнение команды `composer install` должно быть частью процесса разворачивания.   

## Документация

Документация для Bedrock доступна на [https://roots.io/bedrock/docs/](https://roots.io/bedrock/docs/).

[roots-wp-salt]:https://roots.io/salts.html
[wp-cli-dotenv]:https://github.com/aaemnnosttv/wp-cli-dotenv-command

## TODO
- [x] Перевести документацию и комментарии.
- [х] Включить последние версии постоянно используемых плагинов.
- [ ] Автоматизировать загрузку русскоязычных языковых пакетов для плагинов.
- [ ] Автоматизировать возможность смены корневого каталога веб-сервера.
- [ ] Выделить отдельную ветку, идентичную оригинальному пакету и отличающуюся только переводом.
- [ ] Перевести весь объём оригинальной документации.