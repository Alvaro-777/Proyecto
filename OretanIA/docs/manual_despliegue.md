<div align="center">

# Manual de Despliegue
# OretanIA

<br><br>

![](img/logoDocs.png)

<br><br>

**Participantes:** Raúl Lumbreras Delegido, Francisco Manuel Vigil Ruiz, Álvaro Colmenero Rodríguez y Alejandro Medel Martínez  
**Centro:** I.E.S. Oretania  
**Curso:** 2025-2026

</div>

<div style="page-break-after: always;"></div>


---

## Índice

<div style="font-size: 1.2em; line-height: 2.4;">

1. [Introducción](#1-introducción)
2. [Primera Instancia — Servidor Web](#2-primera-instancia--servidor-web)
3. [Segunda Instancia — Base de Datos](#3-segunda-instancia--base-de-datos)
4. [Esquema de la base de datos](#4-esquema-de-la-base-de-datos)

</div>



<div style="page-break-before: always;"></div>

## 1. Introducción

El despliegue de la aplicación **OretanIA** se realizó en la nube de **Amazon Web Services (AWS)** mediante una arquitectura distribuida que separa claramente los roles de servidor web y servidor de base de datos.

Se utilizaron dos instancias **EC2** bajo **Ubuntu Server 22.04 LTS**:

- La **primera instancia** aloja la aplicación web desarrollada con el framework Symfony, Apache como servidor HTTP y un entorno virtual de Python para la ejecución de los servicios de inteligencia artificial (chatbot, análisis predictivo y conversión texto-audio).
- La **segunda instancia** está dedicada exclusivamente al sistema gestor de bases de datos **MySQL 8.0**, garantizando aislamiento, seguridad y rendimiento.

La comunicación entre ambas máquinas se realiza mediante **direcciones IP privadas** dentro de la red VPC de AWS, restringiendo el acceso externo al puerto `3306` únicamente desde la instancia web.

Para facilitar el acceso público, se configuró un nombre de dominio dinámico mediante el servicio gratuito **NO-IP**, asignando el hostname `oretan-ia.ddns.net` a la IP pública elástica de la instancia web. Este dominio se integra con un certificado TLS emitido por **Let's Encrypt**, instalado y renovado automáticamente mediante Certbot, permitiendo el acceso seguro por **HTTPS**.

| Nombre | ID de instancia | Estado | Tipo |
|--------|----------------|--------|------|
| `mi_servidor` | `i-0c605ec63790265f5` | En ejecución | t3.micro |
| `base de datos` | `i-0fde9f7d08f955ac5` | En ejecución | t3.micro |


<div style="page-break-before: always;"></div>

## 2. Primera Instancia — Servidor Web

La primera instancia se desplegó sobre una máquina virtual EC2 de Amazon Web Services, basada en la imagen oficial de **Ubuntu Server 22.04 LTS**. Se configuró con todos los componentes necesarios para servir la aplicación web:

- **Apache 2** como servidor HTTP.
- **PHP 8.3** con las extensiones requeridas por Symfony.
- **Composer** como gestor de dependencias PHP.
- **Python 3** con entorno virtual y librerías de IA.
- **NO-IP** como cliente de DNS dinámico, manteniendo actualizada la resolución del dominio público.
- **Let's Encrypt** mediante Certbot, configurando automáticamente la redirección de tráfico HTTP a HTTPS.

Todos los servicios (Apache, cliente DDNS) están habilitados para iniciarse automáticamente tras reinicios del sistema, asegurando alta disponibilidad sin intervención manual.

### Lista de comandos

| Comando | Descripción |
|---------|-------------|
| `sudo apt install apache2 -y` | Instala el servidor web Apache. |
| `sudo apt install php8.3 php8.3-cli php8.3-common php8.3-mbstring php8.3-xml php8.3-curl php8.3-mysql -y` | Instala PHP 8.3 y las extensiones necesarias para Symfony. |
| `sudo apt install composer -y` | Instala Composer, el gestor de dependencias de PHP. |
| `sudo apt install python3 python3-pip python3-venv -y` | Instala Python 3, pip y el módulo para crear entornos virtuales. |
| `source venv/bin/activate && pip install numpy pandas scikit-learn PyPDF2 python-docx openpyxl xlrd pydub gTTS` | Instala las librerías de Python necesarias para los servicios de IA. |
| `curl ifconfig.me` | Obtiene la IP pública actual de la instancia. |
| `sudo apt install certbot python3-certbot-apache -y` | Instala Certbot y el plugin para Apache. |
| `sudo certbot --apache -d oretan-ia.ddns.net` | Solicita e instala un certificado TLS válido para el dominio. |
| `sudo a2ensite oretan-ia-le-ssl.conf` | Habilita el nuevo sitio SSL. |
| `sudo apache2ctl configtest` | Verifica que la configuración de Apache no tenga errores de sintaxis. |
| `sudo systemctl reload apache2` | Aplica los cambios de configuración de Apache (incluyendo HTTPS). |

### Configuración del VirtualHost

Archivo: `/etc/apache2/sites-available/000-default.conf`

```apache
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/OretanIA/public

    <Directory /var/www/html/OretanIA/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```


<div style="page-break-before: always;"></div>

## 3. Segunda Instancia — Base de Datos

La segunda instancia se desplegó como servidor dedicado de base de datos, ejecutándose sobre una máquina virtual EC2 independiente con **Ubuntu Server 22.04 LTS**. En ella se instaló y configuró **MySQL 8.0** en modo autónomo, optimizado para aceptar conexiones remotas únicamente desde la IP privada de la instancia web (`172.31.15.146`).

Para ello se realizaron los siguientes ajustes:

- Se modificó el parámetro `bind-address` en el archivo `/etc/mysql/mysql.conf.d/mysqld.cnf`, estableciéndolo a `0.0.0.0`.
- Se creó un usuario de base de datos con privilegios restringidos al host `%` para permitir el acceso remoto.
- El **Security Group de AWS** fue configurado para abrir exclusivamente el puerto `3306` (MySQL) al origen específico de la instancia web, garantizando aislamiento de red y seguridad.
- El servicio MySQL se habilitó para iniciarse automáticamente tras reinicios del sistema.

### Lista de comandos

| Comando | Descripción |
|---------|-------------|
| `sudo apt install mysql-server -y` | Instala MySQL 8.0 en el servidor. |
| `sudo mysql_secure_installation` | Ejecuta el asistente de seguridad de MySQL (contraseña root, eliminación de usuarios anónimos, etc.). |
| `sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf` | Edita el archivo de configuración principal de MySQL. |
| `# bind-address = 127.0.0.1` → `bind-address = 0.0.0.0` | Modifica la directiva `bind-address` para permitir conexiones remotas desde cualquier IP. |
| `sudo mysql -u root -p` | Accede a la consola de MySQL como usuario root. |
| `CREATE DATABASE oretania;` | Crea la base de datos `oretania` para la aplicación. |
| `GRANT ALL PRIVILEGES ON oretania.* TO 'oretania_user'@'%';` | Otorga todos los privilegios sobre la base de datos al nuevo usuario. |
| `sudo systemctl enable mysql` | Habilita MySQL para que se inicie automáticamente al arrancar el sistema. |


<div style="page-break-before: always;"></div>

## 4. Esquema de la base de datos

Una vez aplicadas las migraciones de Doctrine, la base de datos `oretania` contiene las siguientes tablas:

```
+------------------------------+
| Tables_in_oretania           |
+------------------------------+
| archivo                      |
| doctrine_migration_versions  |
| historial_uso_ia             |
| ia                           |
| pago                         |
| prediccion                   |
| usuario                      |
+------------------------------+
```

| Tabla | Descripción |
|-------|-------------|
| `usuario` | Almacena los datos de los usuarios registrados. |
| `ia` | Define los servicios de IA disponibles en la plataforma. |
| `historial_uso_ia` | Registra cada uso de un servicio IA por parte de un usuario. |
| `archivo` | Gestiona los ficheros subidos por los usuarios. |
| `pago` | Registra las transacciones de compra de créditos. |
| `prediccion` | Tabla reservada para el servicio de predicción. |
| `doctrine_migration_versions` | Control interno de migraciones ejecutadas por Doctrine. |
