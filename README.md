# D'FANS - Tienda Virtual

## Descripción

D'FANS es una tienda virtual desarrollada para la comercialización de polos con diseños inspirados en diferentes categorías de entretenimiento, música, anime y videojuegos.

El proyecto ha sido desarrollado utilizando WordPress y WooCommerce, incorporando gestión de productos, categorías, variaciones, stock, carrito de compras y configuración de correo electrónico.

---

## Objetivo del proyecto

Desarrollar una plataforma de comercio electrónico funcional que permita a los clientes:

- Visualizar el catálogo de productos.
- Explorar productos por categorías.
- Seleccionar modelo y talla.
- Consultar disponibilidad de stock.
- Añadir productos al carrito.
- Realizar el proceso de compra.
- Recibir comunicaciones relacionadas con la tienda.

---

## Tecnologías utilizadas

- WordPress
- WooCommerce
- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- WP Mail SMTP
- Git
- GitHub
- LocalWP

---

## Estructura de categorías

### Ropa de Adultos

- Anime
- Rock
- Metal
- Artistas
- Películas y Series
- Videojuegos

### Ropa para Niños

- Anime
- Rock
- Metal
- Artistas
- Películas y Series
- Videojuegos

---

## Catálogo

El catálogo inicial está compuesto por 36 diseños.

### Anime

- Demon Slayer
- Dragon Ball
- Naruto
- One Piece
- Pokémon
- Jujutsu Kaisen

### Rock

- AC/DC
- Guns N' Roses
- Nirvana
- Pink Floyd
- Queen
- The Beatles

### Metal

- Black Sabbath
- Iron Maiden
- Megadeth
- Metallica
- Slipknot
- Motörhead

### Artistas

- Bad Bunny
- Eminem
- Tupac
- Travis Scott
- Snoop Dogg
- J Álvarez

### Películas y Series

- Stranger Things
- Breaking Bad
- Prison Break
- The Last of Us
- Game of Thrones
- El Señor de los Anillos

### Videojuegos

- Dota 2
- Fortnite
- Free Fire
- GTA
- League of Legends
- Minecraft

---

## Sistema de productos

Cada producto utiliza un sistema de variaciones basado en:

### Modelo

- Hombre
- Mujer
- Niño
- Niña

### Tallas para adultos

- S
- M
- L
- XL
- XXL

### Tallas infantiles

- 10
- 12
- 14

Cada combinación dispone de control de precio y stock.

---

## Gestión de inventario

El proyecto utiliza el sistema de inventario de WooCommerce para controlar la disponibilidad de las diferentes variaciones de cada producto.

---

## Imágenes de productos

Cada producto puede incluir:

- Vista frontal del polo.
- Vista posterior.
- Modelo masculino.
- Modelo femenino.
- Modelo infantil masculino.
- Modelo infantil femenino.

Las variaciones pueden utilizar galerías específicas para mostrar las imágenes correspondientes al modelo seleccionado.

---

## Precios

Los precios utilizados actualmente para el catálogo son:

- Tallas infantiles: S/ 30
- Tallas adultas S, M y L: S/ 35
- Tallas adultas XL y XXL: S/ 40

Los precios se manejan con impuestos incluidos según la configuración de la tienda.

---

## Comunicación por correo

Se configuró **WP Mail SMTP** utilizando Google/Gmail para el envío de correos electrónicos desde WordPress.

Se realizó una prueba de envío para verificar el correcto funcionamiento del sistema.

---

## Entorno de desarrollo

El proyecto fue desarrollado y probado localmente utilizando **LocalWP**.

Dominio local utilizado:

```text
http://dfans.local
---

## Personalización (código propio)

El código desarrollado para D’FANS está en [`dfans-personalizacion/`](dfans-personalizacion/): snippets, CSS, bloques de la portada y plantillas, y los scripts de mantenimiento.

Funciones principales:

- **Match familiar**: polos unisex con fotos y tallas según la sección (Adultos o Niños) y filtro por público (hombre, mujer, niño, niña).
- **Exclusivos D’FANS**: diseños propios con corte distinto por público (Oversize, Baby tee, Regular reforzado, Manga mariposa).
- **Interacción**: favoritos, vista rápida, vistos recientemente, quiz de fandom, guía de tallas y barra de compra en móvil.
- **Checkout**: delivery en Lima, recojo en Gamarra o envío a provincia; pago con Yape o transferencia.
- **Legal**: Libro de Reclamaciones virtual, Términos y condiciones, Política de privacidad y de cambios.

El paso a producción se hace con **Duplicator** (paquete .zip + installer.php, que no se sube al repositorio).
