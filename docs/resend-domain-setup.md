# Tutorial: Configurar Dominio en Resend para ServiSpin

Este tutorial documenta paso a paso c\u00f3mo verificar el dominio `servispin.net` en Resend para env\u00edos de correo profesionales.

---

## Requisitos previos

- Cuenta en [Resend](https://resend.com) (gratis hasta 3,000 emails/mes)
- Acceso al panel DNS de tu hosting (en este caso: **Blumhost cPanel**)
- Dominio registrado: `servispin.net` (registrado en SquareSpace, DNS gestionado en Blumhost)

---

## Paso 1: Crear cuenta y obtener API Key en Resend

1. Ve a https://resend.com
2. Reg\u00edstrate con tu correo
3. Confirma tu email
4. Ve al dashboard y crea una **API Key** en: **Settings > API Keys**
5. Copia la clave (empieza con `re_`) y gu\u00e1rdala — se muestra una sola vez

---

## Paso 2: Agregar el dominio en Resend

1. En el dashboard de Resend, ve a **Domains > Add Domain**
2. Introduce el dominio: `servispin.net`
3. Selecciona la regi\u00f3n: **Ireland (eu-west-1)** (m\u00e1s cercana a Canarias)
4. Resend mostrar\u00e1 los registros DNS que debes agregar. **Copia la DKIM key** exactamente como aparece

---

## Paso 3: Agregar registros DNS en Blumhost cPanel

1. Entra a tu **cPanel** de Blumhost
2. Busca **DNS Zone Editor** o **Zone Editor** (secci\u00f3n Domains)
3. Selecciona el dominio `servispin.net`
4. Clic en **Add Record**

### Registros a crear:

| Tipo | Host / Name | Valor | TTL | Prioridad |
|------|-------------|-------|-----|-----------|
| **TXT** | `send` | `v=spf1 include:amazonses.com ~all` | Default | — |
| **TXT** | `resend._domainkey` | *(pega la DKIM key exacta del dashboard de Resend)* | Default | — |
| **MX** | `send` | `feedback-smtp.eu-west-1.amazonses.com.` | Default | **10** |
| **TXT** | `_dmarc` | `v=DMARC1; p=none; rua=mailto:postmaster@servispin.net` | Default | — |

### Importante
- El punto final `.` en el MX es **obligatorio** en Blumhost
- La DKIM key debe copiarse **exactamente** del dashboard, sin cortar ni a\u00f1adir espacios
- Todos los registros van bajo el subdominio `send` o `resend._domainkey`, **no** en el dominio ra\u00edz

---

## Paso 4: Verificar el dominio

1. Vuelve al dashboard de Resend
2. Ve a tu dominio `servispin.net`
3. Clic en **"Verify DNS Records"**
4. El estado cambiar\u00e1 a **Verified** (verde) en minutos, a veces horas

> Si despu\u00e9s de 24h no verifica, usa **"Restart verification"** y revisa que los registros coincidan exactamente con los del dashboard.

---

## Paso 5: Configurar Laravel para usar Resend

### 1. Instalar el paquete

```bash
composer require resend/resend-laravel
```

### 2. Configurar `config/mail.php`

Agregar dentro del array `mailers`:

```php
'resend' => [
    'transport' => 'resend',
    'key' => env('RESEND_KEY'),
],
```

### 3. Configurar `.env`

```dotenv
MAIL_MAILER=resend
RESEND_KEY=re_tu_api_key_aqui
MAIL_FROM_ADDRESS="info@servispin.net"
MAIL_FROM_NAME="${APP_NAME}"
MAIL_CC_EMAIL=cesarmilenario@gmail.com
```

> Para pruebas antes de verificar el dominio, puedes usar `MAIL_FROM_ADDRESS="onboarding@resend.dev"`. Solo funciona para tests y aparece en tu dashboard.

### 4. Limpiar cach\u00e9

```bash
php artisan config:clear
php artisan cache:clear
```

---

## Paso 6: Agregar CC en emails de citas (opcional)

En los Mailables de citas (`app/Mail/AppointmentConfirmation.php`, etc.), agregar dentro del `envelope()`:

```php
cc: env('MAIL_CC_EMAIL') ? [new Address(env('MAIL_CC_EMAIL'))] : [],
```

Esto env\u00eda copia oculta (CC) a `cesarmilenario@gmail.com` en cada email de cita.

---

## Paso 7: Probar env\u00edo

1. Crea una cita desde el formulario
2. Revisa el dashboard de Resend: https://resend.com/emails
3. Verifica que el email aparezca como enviado y que llegue al cliente
4. Confirma que el CC llegue a `cesarmilenario@gmail.com`

---

## Troubleshooting

| S\u00edntoma | Causa probable | Soluci\u00f3n |
|------------|----------------|---------|
| "Domain not verified" despu\u00e9s de horas | Registros en DNS equivocados | Revisa que SPF y MX est\u00e9n bajo `send`, DKIM bajo `resend._domainkey` |
| DKIM no reconocido | Key cortada o con espacios extra | Copia exacta del dashboard de Resend |
| MX con dominio duplicado | Falta el punto final `.` | A\u00f1ade `.` al final: `amazonses.com.` |
| "API key missing" | `RESEND_KEY` vac\u00edo o mal escrito | Verifica que empiece con `re_` |
| "Domain not verified" | `info@servispin.net` usado sin verificar | Verifica primero, o usa `onboarding@resend.dev` para tests |

---

## Recursos

- Dashboard de Resend: https://resend.com
- Documentaci\u00f3n oficial: https://resend.com/docs
- Gu\u00eda de verificaci\u00f3n de dominios: https://resend.com/docs/knowledge-base/what-if-my-domain-is-not-verifying
