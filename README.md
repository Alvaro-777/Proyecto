## Pasos para la instalacion en Local

Comandos requeridos

### Crear Archivo .env

```bash
DATABASE_URL="mysql://root:root@127.0.0.1:33100/oretan-ia?serverVersion=8.0"
```

### Crear Base de datos en Doker

```bash
docker run --name oretan-ia  -e MYSQL_ROOT_PASSWORD=root  -e MYSQL_DATABASE="oretan-ia"  -p 33100:3306  -d mysql:8.0  --character-set-server=utf8  --collation-server=utf8_unicode_ci  --init-connect="SET NAMES utf8"
```

### Instalar Composer

```bash
composer require symfony/http-foundation
composer dump-autoload
```

### Iniciar el servidor para visualizar la pagina

```bash
symfony local:server:start
```
