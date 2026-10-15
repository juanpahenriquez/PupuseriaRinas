# Pupusería Rinas

Monolito PHP sobre WampServer. La web pública (landing, menú, nosotros,
ubicación) y el panel de administración (dashboard, productos, pedidos, POS,
usuarios, configuración y horarios) viven en el mismo proyecto.

## Cómo se levanta

1. WampServer encendido (Apache + MySQL).
2. Abrir `http://localhost/PupuseriaRinas/`.

La base `pupuseria_rinas` se crea sola con su esquema y sus datos mínimos la
primera vez que se entra (ver `app/bd.php`). Si MySQL está apagado, las páginas
públicas siguen entrando con los textos por defecto; el panel, en cambio,
necesita la conexión.

## Estructura

```
PupuseriaRinas/
├── index.php  menu.php  nosotros.php  ubicacion.php   Páginas públicas
├── admin/                                              Controladores del panel
├── app/                                                Núcleo
│   ├── inicio.php        Arranque: define RUTA_BASE y carga el núcleo
│   ├── enlaces.php       Constantes de CDN + rinas_head_publico()
│   ├── bd.php            PDO, esquema y config_valor()   [no versionado]
│   ├── sesion.php        Login, sesión y CSRF
│   └── utilerias.php     Formato de horas, paginación, URLs
├── vistas/
│   ├── publicas/         Contenido de las páginas públicas
│   │                     (portada, menu, nosotros, ubicacion)
│   ├── admin/            Vistas del panel
│   │                     (_buscador y _paginacion son parciales)
│   └── plantillas/       cabecera, pie y las tres del panel
│                         (admin_cabeza, admin_cabecera, admin_barra)
├── datos/                Datos importados                    [ignorado por git]
├── herramientas/         Scripts de mantenimiento
├── assets/               css, img, js, video
└── .gitignore
```

### El CSS

Todo el frontend usa **un solo archivo**: `assets/css/estilos.css` (~226 KB),
con cuatro bloques en este orden:

| Bloque    | Qué lleva                                                |
|-----------|----------------------------------------------------------|
| `BASE`    | tokens `:root`, cabecera, pie y páginas públicas (`.px-*`) |
| `LANDING` | lenguaje visual de la portada (`.nl-*`)                  |
| `PANEL`   | administración (`.admin-*`)                             |
| `POS`     | punto de venta (`.pos-*`)                               |

El orden no es arbitrario: `BASE` va primero porque el resto usa sus variables
(`--rinas-azul`, `--rinas-crema`, `--font-titulos`). Dentro de `BASE`, el
bloque `px-` de las páginas públicas va al final a propósito, para poder pisar
a las clases `.nl-*`.

Todo se versiona con una sola constante, `LINK_CSS_VERSION`
(`app/enlaces.php`). Antes cada hoja llevaba su propio `?v=` y era fácil subir
una y olvidar otra.

Ojo al añadir selectores globales (`html`, `body`, `*`): como todo vive en el
mismo archivo, una regla suelta afecta a todas las páginas. Ya pasó con
`html { overflow: hidden }` del POS, que habría dejado la web pública sin
scroll; por eso ahora va como `html:has(body.pos-body)`.

### Las dos reglas que hay que respetar al añadir cosas

**1. Las vistas no son páginas.** Una vista de `vistas/` se incluye desde un
controlador (`admin/pedidos.php` o `index.php`). Por eso las rutas relativas
del markup de una vista están pensadas para el navegador, no para el archivo:
`../assets/img/logo.jpg` dentro de `vistas/admin/` apunta a `/assets/…`
porque la vista se sirve desde `/admin/`.

**2. Para el núcleo, siempre `app/inicio.php`.**

```php
require_once __DIR__ . '/../app/inicio.php';   // desde admin/
require_once __DIR__ . '/../../app/inicio.php'; // desde vistas/

require_once __DIR__ . '/../app/sesion.php';   // solo si necesitas sesión
```

`app/inicio.php` carga enlaces, utilerias y bd una sola vez. La sesión **no**
se carga ahí a propósito: las páginas públicas no la necesitan y así no se
abre una sesión en cada visita.

## Comprobar que nada está roto

```
php herramientas/revisar_proyecto.php
```

Recorre todos los `.php` y avisa de includes que no existen, de páginas
enlazadas que no están y de recursos que faltan. Sale con código 1 si hay
algún fallo, así que sirve como paso previo a commit.

## Historial

`app/bd.php` y `datos/productos.json` están fuera de git a propósito: el
primero tiene las credenciales de MySQL y el hash del administrador.