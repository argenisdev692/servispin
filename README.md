# SERVISPIN — Sistema de gestión de citas y asistencia técnica

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?logo=php&logoColor=white)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3.8-FB70A9)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.x_(CDN)-38B2AC?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

Plataforma web para **Servispin** (reparación de electrodomésticos en Gran Canaria): landing pública, reserva de citas a domicilio, **asistencia técnica remota** por videollamada con verificación de pago SumUp, panel de administración y API REST.

**Producción:** [https://servispin.net](https://servispin.net)

![Landing principal](https://servispin.net/files/images/screenshot.webp)

---

## Tabla de contenidos

- [Módulos](#módulos)
- [Stack tecnológico](#stack-tecnológico)
- [URLs](#urls)
- [Inicio rápido](#inicio-rápido)
- [Documentación](#documentación)
- [Testing](#testing)
- [Despliegue](#despliegue)
- [Licencia](#licencia)

---

## Módulos

| Módulo | Descripción | Ruta principal |
|--------|-------------|----------------|
| **Landing pública** | Home SSR con hero, servicios, galería, testimonios, GDPR cookies | `GET /` |
| **Citas presenciales** | Reserva a domicilio con foto del aparato | `/appointments/book` |
| **Asistencia remota** | Funnel público: pago SumUp + solicitud videollamada | `/asistencia-remota` |
| **Bandeja pagos remotos** | Cotejo manual de referencias SumUp | `/admin/remote-assistance` |
| **Historial de pagos** | Timeline de eventos de pago remoto | `/admin/remote-assistance/pagos` |
| **Calendario admin** | FullCalendar con citas presenciales y remotas | `/admin/appointment-calendar` |
| **Disponibilidad** | Reglas semanales y excepciones (festivos, cierres) | Admin availability |
| **Servicios y marcas** | CRUD de servicios (duración, remoto, precio) y marcas | `/admin/services`, `/brands` |
| **Galería** | Imágenes de la home | `/gallery-images` |
| **Blog / posts** | Contenido con Livewire | `/posts` |
| **Usuarios y roles** | Jetstream + Spatie Permission | `/users`, `/dashboard` |
| **Datos de empresa** | Logo, contacto, SEO empresa | `/company-data` |
| **Contacto** | Formulario público con throttle | `POST /contact/submit` |
| **Legal** | Privacidad, cookies, aviso legal | `/privacidad`, `/cookies`, `/aviso-legal` |
| **Google Meet** | Generación manual/automática de enlaces (Calendar API) | Ver `docs/ssd/001-asistencia-remota/` |
| **Email transaccional** | Confirmaciones, recordatorios, remoto | Resend |
| **SEO** | Meta tags (Artesaos SEOTools) | Vistas admin/blog |
| **Almacenamiento fotos** | Fotos de citas en disco `public` | `storage/app/public/appointment_photos/` |

---

## Stack tecnológico

### Backend (`composer.json`)

| Paquete | Versión requerida | Notas |
|---------|-------------------|-------|
| PHP | ^8.4 | |
| Laravel Framework | ^13.8 | v13.20+ |
| Livewire | ^3.8.2 | CRUD admin |
| Laravel Jetstream | ^5.5 | Auth, 2FA, perfil |
| Laravel Sanctum | ^4.3 | API tokens |
| Laravel Socialite | ^5.28 | Google OAuth |
| Intervention Image Laravel | ^4.1 | Resize fotos citas |
| Spatie Laravel Permission | ^8.3 | Roles Admin/Usuario |
| Spatie Google Calendar | ^3.8 | Enlaces Meet |
| Resend Laravel | ^1.4 | Email transaccional |
| Artesaos SEOTools | ^1.4 | SEO meta |
| Flysystem AWS S3 | ^3.32 | Supabase legacy (opcional) |

### Frontend

| Capa | Tecnología | Uso |
|------|------------|-----|
| Landing + formularios públicos | Tailwind CSS **4.x** (browser CDN) | `welcome.blade.php`, citas, remoto |
| Panel admin | Tailwind 3.x + Vite + Alpine.js | CRUD, calendario, sidebar |
| Tipografía | Plus Jakarta Sans | Global |
| Build | Vite 4 + npm | Assets admin (`npm run build`) |

### DevOps

- **Laravel Sail** (Docker) — entorno local
- **PHPUnit 12** — tests (incl. suite asistencia remota)
- **Laravel Pint** — estilo PHP

---

## URLs

| Entorno | URL |
|---------|-----|
| **Producción** | https://servispin.net |
| **Reserva presencial** | https://servispin.net/appointments/book |
| **Asistencia remota** | https://servispin.net/asistencia-remota |
| **Panel admin** | https://servispin.net/dashboard |
| **API base** | https://servispin.net/api |
| **Desarrollo (Sail)** | http://localhost |

> En producción, configurar `APP_URL=https://servispin.net` en `.env` (afecta enlaces de storage, emails y URLs generadas).

---

## Inicio rápido

### Prerrequisitos

- Docker Desktop (Sail) o PHP 8.4 + MySQL 8+
- Composer 2.7+
- Node.js 18+ (build assets admin)

### Instalación con Sail

```bash
git clone <repo-url> servispin && cd servispin
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link
npm install && npm run build
```

Abrir en desarrollo: **http://localhost**

### Verificación

```bash
./vendor/bin/sail artisan test
```

---

## Documentación

| Documento | Contenido |
|-----------|-----------|
| [docs/landing/README.md](docs/landing/README.md) | Landing pública, SEO, performance, screenshots |
| [docs/ssd/001-asistencia-remota/](docs/ssd/001-asistencia-remota/) | Spec, plan y tareas del módulo remoto |
| [docs/ssd/001-asistencia-remota/README-google-meet.md](docs/ssd/001-asistencia-remota/README-google-meet.md) | OAuth Google Meet / Calendar |
| [docs/modulo-asistencia-tecnico.md](docs/modulo-asistencia-tecnico.md) | Notas de producto (SumUp, videollamada) |

### Screenshots

| Vista | URL |
|-------|-----|
| Landing hero | https://servispin.net/files/images/screenshot.webp |
| Promo asistencia remota | https://servispin.net/files/images/asistencia-online.webp |

---

## Testing

```bash
# Todos los tests
./vendor/bin/sail artisan test

# Solo asistencia remota
./vendor/bin/sail artisan test --filter=RemoteAssistance
```

Cobertura principal: reservas, disponibilidad, verificación de pago, cancelaciones, recordatorios, calendario admin.

---

## Despliegue

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan storage:link
```

Variables críticas en producción:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://servispin.net
MAIL_MAILER=resend
```

---

## Licencia

MIT — ver [LICENSE](LICENSE).

**Servispin** · Las Palmas de Gran Canaria · [servispin.net](https://servispin.net)
