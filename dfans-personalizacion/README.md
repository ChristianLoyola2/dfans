# Personalización de D’FANS

Casi todo lo propio de la tienda vive en la **base de datos** de WordPress, no en archivos. Esta carpeta guarda una copia legible para el repositorio:

| Carpeta | Qué contiene |
|---|---|
| `snippets/` | Los 16 fragmentos activos de *Code Snippets*: checkout por tipo de entrega, Yape, tallas, secciones Match/Adultos/Niños, Libro de Reclamaciones, interacción fandom (favoritos, vista rápida, quiz, guía de tallas)… |
| `css/` | CSS adicional de los estilos globales del tema (Twenty Twenty-Five). |
| `bloques/` | HTML de bloques de la portada, cabecera, pie y plantillas de las secciones Adultos y Niños. |
| `scripts/` | Scripts PHP/Python usados para aplicar los cambios por consola (LocalWP). |

La copia activa está en la base de datos: editar estos archivos **no** cambia la tienda.
Los datos legales (RUC y razón social) del snippet 26 son **ficticios** hasta el lanzamiento.

No se incluyen: `wp-config.php`, copias de la base de datos, pedidos, datos de clientes, claves de pago, credenciales SMTP ni tokens OAuth.
