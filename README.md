
<p align="center">
  <a href="https://www.php.net/"><img alt="PHP" src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white"/></a>
  <a href="https://laravel.com/"><img alt="Laravel" src="https://img.shields.io/badge/Laravel-F05340?style=for-the-badge&logo=laravel&logoColor=white"/></a>
  <a href="https://www.mysql.com/"><img alt="MySQL" src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white"/></a>
  <a href="https://openrouter.ai/"><img alt="OpenRouter API" src="https://img.shields.io/badge/OpenRouter_API-000000?style=for-the-badge&logo=OpenAI&logoColor=white"/></a>
</p>


# 🧠 VMentions - Backend

![VMentions Logo](VMentionsLogo.png)


> 🎯 **Plataforma inteligente para analizar menciones con IA.**  
> Backend desarrollado en PHP para procesar alertas, gestionar usuarios y alimentar el panel de administración.

---

## ⚙️ ¿Qué hace este backend?

- 🔄 Procesa alertas de menciones desde Google Alerts RSS
- 📩 Envía informes automáticos mensuales a cada cliente
- 🧠 Analiza contenido con IA (temática y sentimiento)
- 🧑‍💼 Panel de administración para gestionar usuarios, alertas y datos

---

## 📦 Estructura del proyecto

```
📂 v_mentions_backend/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── ProcesarMencionesRSS.php
│   │       └── ResumenMensual.php
│   ├── Exceptions/
│   │   └── Handler.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── MentionController.php
│   │   │   ├── UserController.php
│   │   │   ├── AlertController.php
│   │   │   └── DashboardController.php
│   │   ├── Kernel.php
│   │   └── Middleware/
│   │       ├── Authenticate.php
│   │       ├── CheckForMaintenanceMode.php
│   │       ├── EncryptCookies.php
│   │       ├── RedirectIfAuthenticated.php
│   │       ├── TrimStrings.php
│   │       └── VerifyCsrfToken.php
│   ├── Mail/
│   │   ├── ResumenMensualMail.php
│   │   └── AlertaNuevaMail.php
│   ├── Models/
│   │   ├── Mention.php
│   │   ├── User.php
│   │   └── Alert.php
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   └── RouteServiceProvider.php
│   └── Services/
│       ├── IAService.php
│       └── ExportService.php
├── bootstrap/
│   └── app.php
├── config/
│   ├── app.php
│   ├── database.php
│   └── mail.php
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 2024_01_01_000000_create_users_table.php
│   │   ├── 2024_01_01_000001_create_alerts_table.php
│   │   └── 2024_01_01_000002_create_mentions_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── public/
│   ├── main.php
│   └── index.php
├── resources/
│   └── views/
│       ├── welcome.blade.php
│       └── emails/
│           ├── resumen.blade.php
│           └── alerta.blade.php
├── routes/
│   ├── web.php
│   └── api.php
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
│   └── Feature/
│       └── MentionTest.php
├── .env
├── .env.example
├── .gitignore
├── artisan
├── composer.json
├── composer.lock
├── docker-compose.yml
├── sail
└── README.md

```

---

## 🚀 ¿Cómo arrancarlo?

```bash
# Clona el repositorio
git clone https://github.com/dalpe23/v_mentions_backend.git

# Copia archivo .env y genera la clave
cp .env.example .env

# Levanta el entorno con Docker + Sail
./vendor/bin/sail up -d

# Instala dependencias dentro del contenedor
./vendor/bin/sail composer install

# Ejecuta migraciones
./vendor/bin/sail php artisan migrate

# Lanza los comandos manualmente o añade al cron
./vendor/bin/sail php artisan app:procesar-menciones-rss
./vendor/bin/sail php artisan app:enviar-resumen-menciones
```

---

## 📅 Tareas programadas

| Tarea                    | Frecuencia   | Acción                                                             |
|-------------------------|--------------|--------------------------------------------------------------------|
| `ProcesarMencionesRSS`  | Cada día  | Recupera nuevas menciones y las analiza con IA                    |
| `EnviarResumenMenciones`        | Mensual      | Envía email con las menciones negativas del mes a cada cliente    |

> 🧪 *Estas tareas están programadas mediante `cron`.*

---

## 🔐 Seguridad

- `.env` está excluido del repositorio con `.gitignore`
- Las credenciales se cargan desde entorno seguro

---

## ✨ Características destacadas

|Ventajas de VMentions |
----------------------------------------------|
| ✅ Panel unificado de menciones|
| ✅ Filtros y exportación | 
| ✅ IA para sentimiento y temática | 
| ✅ Sin coste de licencias |
---

## 🧪 Frontend en Vue

El frontend de este proyecto se encuentra en el siguiente repositorio:

👉 [**v_mentions_frontend**](https://github.com/dalpe23/v_mentions_frontend)

---

## 📫 Contacto

Creado con ❤️ por [dalpe23](https://github.com/dalpe23)

[![LinkedIn](https://img.shields.io/badge/LinkedIn-Contacto-blue?style=for-the-badge&logo=linkedin)](https://www.linkedin.com/in/daniel-alemany-p%C3%A9rez-256b57354/)

---

> ℹ️ ¿Ideas o mejoras? ¡Abre un issue o haz un pull request!


---
---
---

# 🧠 VMentions - Backend (English)

> 🎯 **Smart platform to analyze mentions using AI.**  
> Backend built in PHP to process alerts, manage users, and power the admin panel.

---

## ⚙️ What does this backend do?

- 🔄 Processes mention alerts from Google Alerts RSS
- 📩 Sends monthly automatic reports to each client
- 🧠 Analyzes content with AI (topic and sentiment)
- 🧑‍💼 Admin panel to manage users, alerts, and data

---

## 📦 Project structure

```

📂 v_mentions_backend/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── ProcesarMencionesRSS.php
│   │       └── ResumenMensual.php
│   ├── Exceptions/
│   │   └── Handler.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── MentionController.php
│   │   │   ├── UserController.php
│   │   │   ├── AlertController.php
│   │   │   └── DashboardController.php
│   │   ├── Kernel.php
│   │   └── Middleware/
│   │       ├── Authenticate.php
│   │       ├── CheckForMaintenanceMode.php
│   │       ├── EncryptCookies.php
│   │       ├── RedirectIfAuthenticated.php
│   │       ├── TrimStrings.php
│   │       └── VerifyCsrfToken.php
│   ├── Mail/
│   │   ├── ResumenMensualMail.php
│   │   └── AlertaNuevaMail.php
│   ├── Models/
│   │   ├── Mention.php
│   │   ├── User.php
│   │   └── Alert.php
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   └── RouteServiceProvider.php
│   └── Services/
│       ├── IAService.php
│       └── ExportService.php
├── bootstrap/
│   └── app.php
├── config/
│   ├── app.php
│   ├── database.php
│   └── mail.php
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 2024_01_01_000000_create_users_table.php
│   │   ├── 2024_01_01_000001_create_alerts_table.php
│   │   └── 2024_01_01_000002_create_mentions_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── public/
│   ├── main.php
│   └── index.php
├── resources/
│   └── views/
│       ├── welcome.blade.php
│       └── emails/
│           ├── resumen.blade.php
│           └── alerta.blade.php
├── routes/
│   ├── web.php
│   └── api.php
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
│   └── Feature/
│       └── MentionTest.php
├── .env
├── .env.example
├── .gitignore
├── artisan
├── composer.json
├── composer.lock
├── docker-compose.yml
├── sail
└── README.md

```

---

## 🚀 How to run it?

```bash
# Clone the repository
git clone https://github.com/dalpe23/v_mentions_backend.git

# Copy the .env file and generate the key
cp .env.example .env

# Start the environment with Docker + Sail
./vendor/bin/sail up -d

# Install dependencies inside the container
./vendor/bin/sail composer install

# Run migrations
./vendor/bin/sail php artisan migrate

# Run commands manually or schedule them
./vendor/bin/sail php artisan app:procesar-menciones-rss
./vendor/bin/sail php artisan app:enviar-resumen-menciones
```

---

## 📅 Scheduled tasks

| Task                        | Frequency  | Action                                                             |
|-----------------------------|------------|--------------------------------------------------------------------|
| `ProcesarMencionesRSS`      | Daily      | Retrieves new mentions and analyzes them with AI                  |
| `EnviarResumenMenciones`    | Monthly    | Sends a summary email of negative mentions to each client         |

> 🧪 *These tasks are scheduled using `cron`.*

---

## 🔐 Security

- `.env` is excluded from the repository via `.gitignore`
- Credentials are loaded from a secure environment

---

## ✨ Key features

| Advantages of VMentions |
|--------------------------|
| ✅ Unified mention panel |
| ✅ Filters and export    |
| ✅ AI-based sentiment and topic analysis |
| ✅ No licensing costs    |

---

## 🧪 Frontend in Vue

The frontend of this project can be found in the following repository:

👉 [**v_mentions_frontend**](https://github.com/dalpe23/v_mentions_frontend)

---

## 📫 Contact

Made with ❤️ by [dalpe23](https://github.com/dalpe23)

[![LinkedIn](https://img.shields.io/badge/LinkedIn-Contact-blue?style=for-the-badge&logo=linkedin)](https://www.linkedin.com/in/daniel-alemany-p%C3%A9rez-256b57354/)

---

> ℹ️ Ideas or improvements? Open an issue or submit a pull request!
