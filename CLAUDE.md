# Budget Manager - Claude Code Configuration

## 🎯 Przegląd Projektu
Budget Manager to aplikacja MVP do zarządzania budżetami osobistymi stworzona w PHP z Symfony 6.4 LTS.

## 🛠️ Stack Technologiczny
- **Backend**: PHP 8.1, Symfony 6.4 LTS
- **Frontend**: Bootstrap 5, Stimulus, SCSS
- **Baza danych**: SQLite (development)
- **Autoryzacja**: JWT Tokens z RSA
- **Build tools**: Webpack Encore, Composer

## 📋 Komendy Deweloperskie

### Podstawowe komendy
```bash
# Instalacja zależności
composer install
npm install

# Kompilacja zasobów
npm run dev        # Development
npm run watch      # Development z watch
npm run build      # Production

# Serwer deweloperski
symfony server:start
# lub
php -S localhost:8000 -t public/
```

### Baza danych
```bash
# Resetowanie całej bazy (CUSTOM COMMAND!)
php bin/console app:db:reset --force

# Standardowe komendy Doctrine
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### Inne przydatne komendy
```bash
# Wygenerowanie JWT kluczy
php bin/console lexik:jwt:generate-keypair

# Cache
php bin/console cache:clear

# Debug
php bin/console debug:router
php bin/console debug:container
```

## 🏗️ Architektura

### Entity Model
- **User**: Użytkownicy z flagą `isActive` i rolą `ROLE_USER`
- **Budget**: Budżety przypisane do użytkowników
- **Transaction**: Transakcje (income/expense) przypisane do budżetów

### API Endpoints
- `POST /api/register` - Rejestracja
- `POST /api/login` - Logowanie (JWT)
- `GET /api/budgets` - Lista budżetów
- `POST /api/budgets` - Tworzenie budżetu
- `GET /api/budgets/{id}` - Szczegóły budżetu
- `GET /api/budgets/{id}/transactions` - Lista transakcji
- `POST /api/budgets/{id}/transactions` - Dodanie transakcji

### Web Interface
- `/` - Dashboard
- `/login` - Logowanie
- `/register` - Rejestracja
- `/budgets` - Zarządzanie budżetami

## 📚 Dokumentacja API
OpenAPI/Swagger dostępna pod: `/api/doc`

## 🔧 Konfiguracja

### Zmienne środowiskowe (.env)
```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_dev.db"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=XYZ123
```

### Konto testowe
- **Email**: test@example.com
- **Password**: password123

## 🚨 Ważne Uwagi

### Bezpieczeństwo
- Klucze JWT są w .gitignore
- Hasła są haszowane bcrypt
- CSRF protection dla formularzy
- Walidacja danych wejściowych

### Migracje
Migracje mają uporządkowane numery:
- `Version20250101010000.php` - Inicjalna struktura

### Custom Command
`php bin/console app:db:reset` - bezpieczne resetowanie bazy bez kasowania pliku DB

## 🐛 Troubleshooting

### Problemy z migracjami
```bash
php bin/console app:db:reset --force
```

### Problemy z JWT
```bash
php bin/console lexik:jwt:generate-keypair --overwrite
```

### Problemy z Webpack
```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

## 📝 TODO / Rozszerzenia
- Kategorie transakcji
- Eksport danych CSV/Excel
- Wykresy i statystyki
- Powiadomienia o limitach
- Aplikacja mobilna
- Integracje bankowe

## 🔗 Przydatne Linki
- [Symfony Docs](https://symfony.com/doc)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)
- [Bootstrap 5](https://getbootstrap.com/)
- [Stimulus](https://stimulus.hotwired.dev/)