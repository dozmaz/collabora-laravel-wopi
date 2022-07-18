<p align="center"><a href="https://guidocutipa.blog.bo" target="_blank"><img src="https://i0.wp.com/guidocutipa.blog.bo/wp-content/uploads/2018/10/logo-1.png?fit=210%2C49&ssl=1" width="200"></a></p>

<p align="center">
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Acerca del paquete

Un ejemplo simple integrando Collabora Online a través de iFrame con Laravel. Suponemos que ya tiene instalado e iniciado el servidor web Apache en su máquina, y que el módulo PHP para Apache también se ha instalado y cargado

## Instrucciones

### Instalar el servidor de Collabora Online

Seguir las instrucciones de [instalación de Collabora Online](https://sdk.collaboraonline.com/docs/installation/index.html), también se pueden seguir las instrucciones de la página oficial [instalación de Collabora Online](https://sdk.collaboraonline.com/docs/installation/index.html)

Después de la instalación debe obtener una URL similar a [http://192.168.10.33:9980](http://192.168.10.33:9980)


### Instalar dependencias

``` bash
# clonar repositorio
$ git clone https://github.com/dozmaz/collabora-laravel-wopi.git guidocutipa-wopi-project

# ir al directorio clonado
$ cd guidocutipa-wopi-project

# instalar dependencias
$ composer install

# instalar dependencias
$ npm install
```

### configurar APP_URL

> Si la URL del proyecto se parece a: guidocutipa.blog.bo/wopi-project
Entonces ir a `guidocutipa-wopi-project/.env`
Y modificar esta línea:

* APP_URL =

Para que se parezca a:

* APP_URL = http://guidocutipa.blog.bo/wopi-project

### configurar WOPI_CLIENT_URL

> Ir a `guidocutipa-wopi-project/.env`
Y modificar esta línea:

* WOPI_CLIENT_URL =

Para que se parezca a:

* WOPI_CLIENT_URL = https://192.168.10.33:9980

### configurar WOPI_NO_SSL_VALIDATION

> Si WOPI_CLIENT_URL utiliza un certificado SSL autofirmado se debe cambiar la configuración. 
Ir a `guidocutipa-wopi-project/.env`
Y modificar esta línea:

* WOPI_NO_SSL_VALIDATION = false

Para que se parezca a:

* WOPI_NO_SSL_VALIDATION = true


### Siguiente paso

``` bash
# Dentro del directorio de la aplicación
# generar el APP_KEY de laravel
$ php artisan key:generate
```

## Modo de Uso

``` bash
# iniciar el servidor local
$ php artisan serve
```

>Nota: Collabora Online y la aplicación Laravel deben ejecutarse en el mismo protocolo es decir HTTP o HTTPS, caso contrario se producirán errores que no permitiran ejecutar correctamente el ejemplo.
