# Bharat SEO — Premium Digital Marketing Agency Website

**Grow Your Brand. Scale Your Reach.**

A complete, production-ready website + admin panel for a Social Media Marketing, SEO, Paid Ads, Branding, Web Design & Lead Generation agency. Built with **Core PHP 8+, MySQL, Bootstrap-grade responsive CSS, vanilla JS and Three.js** — no Node.js, React, Laravel or build step required. Upload to any standard PHP shared host, import the database, set your config, and go live.

---

## ✨ Highlights

- **Premium dark theme** (with light mode toggle), glassmorphism, smooth scroll animations and micro-interactions.
- **Three.js animated network globe** in the hero — lazy-loaded and automatically disabled on low-end / mobile devices with a branded static fallback.
- **Fully dynamic & editable** — every section, service, blog, portfolio item, package, testimonial, FAQ, menu and setting is managed from the admin panel.
- **Complete SEO system** — dynamic meta tags, Open Graph, Twitter cards, JSON-LD schema (Organization, WebSite, LocalBusiness, Service, FAQ, Article, Breadcrumb), XML sitemap, robots.txt, canonical URLs, per-page SEO overrides, 301 redirect manager.
- **Lead engine** — free audit form, contact form, package & service inquiries, newsletter, WhatsApp deep links, with a full CRM-style admin (statuses, notes, assignment, CSV export, email notifications).
- **Secure admin panel** at `/admin/` with role-based access, CSRF protection, password hashing, login lockout, password reset, activity logging and database backup.
- **Never-break architecture** — graceful fallbacks for missing data, missing images, empty sections, DB outages, disabled JS and failed Three.js; nothing overflows the screen on any device.

---

## 🧰 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Core PHP 8+ (PDO, prepared statements) |
| Database | MySQL / MariaDB (InnoDB, utf8mb4) |
| Frontend | HTML5, CSS3 (Grid/Flexbox/clamp), vanilla JS |
| 3D | Three.js (r128, lazy-loaded from CDN) |
| Fonts/Icons | Google Fonts (Sora + Inter), Font Awesome 6 (CDN) |

---

## 📦 Requirements

- PHP **8.0+** with PDO, pdo_mysql, fileinfo, mbstring extensions
- MySQL **5.7+** or MariaDB **10.3+**
- Apache with `mod_rewrite` (an `.htaccess` is included). Nginx works too (see notes below).

---

## 🚀 Installation

### 1. Upload files
Upload the entire project to your web root (e.g. `public_html`) or a subfolder.

### 2. Create the database & import
Create a MySQL database, then import the schema + sample data:

```bash
mysql -u YOUR_USER -p YOUR_DB < database/database.sql
```
Or use **phpMyAdmin → Import → choose `database/database.sql`**.

This creates all tables and seeds sample services, blogs, portfolio, case studies, testimonials, packages, FAQs, menus and demo admin accounts.

### 3. Configure
Copy the example config and edit it:

```bash
cp config/config.example.php config/config.php
```

Edit `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
define('SITE_URL', 'https://www.yourdomain.com'); // no trailing slash
define('APP_ENV', 'production');                  // 'production' hides errors
define('APP_KEY', 'change-this-to-a-long-random-string');
```

> If `config/config.php` is absent, the app falls back to `config.example.php` and auto-detects the host URL — handy for first-run, but create a real `config.php` for production.

### 4. Permissions
Make these writable by the web server:

```
assets/uploads/   (image uploads & media library)
logs/             (error & rate-limit logs)
```

```bash
chmod -R 755 assets/uploads logs
```

### 5. Done
Visit your domain. Log in to the admin panel at:

```
https://www.yourdomain.com/admin/
```

---

## 🔐 Demo Admin Credentials

All demo accounts use the password **`Admin@123`**. **Change these immediately in production** (Admin → Users & Roles).

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `admin@bharatseo.com` | `Admin@123` |
| Developer / SEO Manager | `seo@bharatseo.com` | `Admin@123` |
| Content Manager | `content@bharatseo.com` | `Admin@123` |
| Sales Manager | `sales@bharatseo.com` | `Admin@123` |

---

## 🗂️ Project Structure

```
/
├── index.php               Front controller / router
├── robots.php              Dynamic robots.txt  (served as /robots.txt)
├── sitemap.php             Dynamic XML sitemap (served as /sitemap.xml)
├── .htaccess               Routing, security headers, caching, error docs
├── config/
│   ├── config.php          Your live config (create from example)
│   └── config.example.php  Config template
├── includes/               Core libraries (bootstrap, db, functions, security, seo)
│   ├── header.php / navbar.php / footer.php   Reusable layout components
├── templates/              Public page templates + partials + error pages
├── api/                    Form endpoints (lead, contact, comment, newsletter)
├── admin/                  Secure admin panel
│   ├── login.php / logout.php / forgot-password.php / reset-password.php
│   ├── index.php           Dashboard
│   ├── includes/           Admin auth, layout, CRUD engine, helpers
│   └── pages/              All CRUD modules
├── assets/
│   ├── css/style.css       Public design system
│   ├── js/main.js          Public interactions
│   ├── js/three-hero.js    Lazy-loaded 3D hero
│   └── uploads/            Uploaded media (writable)
├── database/database.sql   Full schema + seed data
└── logs/                   Protected logs (writable)
```

---

## 📄 Public Pages

Home, About, Services (+ dynamic detail pages for all 12 services), Portfolio (+ detail), Case Studies (+ detail), Pricing, Industries, Blog (+ category/search/detail), Free Audit, Contact, Privacy Policy, Terms & Conditions, Disclaimer, HTML Sitemap, plus custom 404 / 500 / maintenance / access-denied pages.

Clean URLs are powered by `.htaccess` rewriting to the front controller, e.g.:

- `/service/instagram-marketing`
- `/blog/seo-trends-2026`
- `/portfolio/urbancart-meta-ads`
- `/case-studies/urbancart-5-8x-roas`

---

## 🛠️ Admin Modules

Dashboard · Leads (with notes, status, assignment, CSV export) · Contact Messages · Newsletter · Pricing Packages · Payments · Blog Posts · Blog Categories · Comments (moderation) · Portfolio · Case Studies · Testimonials · FAQs · Team · Client Logos · Pages (CMS) · Media Library · Services · Service Categories · Page SEO · Redirects · SEO Tools · Site Settings (general, contact, SEO/analytics, custom scripts, SMTP, payments) · Menus · Social Links · Agency Stats · Users & Roles · Activity Log · Database Backup.

### Roles & Permissions
- **Super Admin** — full access.
- **SEO Manager** — services, SEO, redirects, pages, portfolio, case studies, media.
- **Content Manager** — blogs, comments, pages, FAQs, testimonials, team, media.
- **Sales Manager** — leads, contact messages, newsletter, packages, payments.

---

## 🔎 SEO

- Dynamic `<title>`, meta description, keywords, canonical, robots per page.
- Open Graph + Twitter Card tags on every page.
- JSON-LD: Organization, WebSite (+ SearchAction), LocalBusiness, Service, FAQPage, Article, BreadcrumbList.
- Auto-generated `sitemap.xml` and `robots.txt` (both editable via admin).
- Per-page SEO overrides + 301/302 redirect manager + no-index toggle.
- Set your **Google Analytics ID**, **Search Console verification**, and **Meta Pixel** under *Admin → Settings → SEO & Analytics*.

---

## ✉️ Email / SMTP

By default the site uses PHP's `mail()`. For reliable delivery, configure SMTP under *Admin → Settings → Email / SMTP* (host, port, username, password, encryption). Lead/contact notifications are sent to the **Lead Notification Email** set there.

---

## 💳 Payments

Add a **payment/checkout link** to any pricing package (e.g. a Razorpay/PayU payment-page link) and the package button will use it. Gateway keys can be stored under *Admin → Settings → Payments*, and the **Payments** module tracks transaction records and statuses. Payment success/failure landing pages are included.

---

## 🛡️ Security

- PDO prepared statements everywhere (no string-built SQL).
- Output escaping (`e()`), allow-listed HTML sanitization for rich content.
- CSRF tokens on all forms; honeypot + time-trap + rate limiting on public forms.
- `password_hash()` / `password_verify()`, login attempt lockout, session regeneration & secure cookies.
- Secure file-upload validation (extension + MIME + size); uploads directory blocks script execution.
- Sensitive directories (`config/`, `includes/`, `logs/`) denied via `.htaccess`; admin is `noindex`.
- Friendly error pages — raw SQL/PHP errors are never shown to visitors (logged to `/logs`).

> After install: set `APP_ENV` to `production`, change `APP_KEY`, and update all demo passwords.

---

## ⚙️ Nginx (optional)

If you use Nginx instead of Apache, add a fallback to the front controller:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
location = /robots.txt { rewrite ^ /robots.php; }
location = /sitemap.xml { rewrite ^ /sitemap.php; }
location ~ ^/(config|includes|logs)/ { deny all; }
```

---

## 🧪 Post-Install Checklist

- [ ] Import `database/database.sql`
- [ ] Create `config/config.php` with DB + SITE_URL + APP_KEY
- [ ] Make `assets/uploads/` and `logs/` writable
- [ ] Log in to `/admin/` and change all demo passwords
- [ ] Set business details, contact info, WhatsApp number (Settings → Contact)
- [ ] Add Google Analytics / Search Console / Pixel (Settings → SEO)
- [ ] Configure SMTP for email notifications (Settings → Email)
- [ ] Replace sample services / blogs / portfolio / testimonials with your own
- [ ] Submit `sitemap.xml` to Google Search Console
- [ ] Set `APP_ENV` to `production`

---

© Bharat SEO. Built for performance, conversions and growth.
