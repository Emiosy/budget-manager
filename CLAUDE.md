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

# Testy
php vendor/bin/phpunit                   # Wszystkie testy
php vendor/bin/phpunit tests/Entity/     # Tylko testy encji
php vendor/bin/phpunit tests/DTO/        # Tylko testy DTO
php vendor/bin/phpunit tests/Controller/ # Tylko testy kontrolerów
php vendor/bin/phpunit --coverage-text   # Z pokryciem (wymaga Xdebug)
php vendor/bin/phpunit --testdox         # Czytelny format wyników

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
OpenAPI/Swagger dostępna pod: `/api/docs` (dostępna bez autoryzacji)

## 🔧 Konfiguracja

### Zmienne środowiskowe (.env)
```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_dev.db"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=XYZ123
```

### Konta testowe
- **Test User**: test@example.com | password123
- **Anna Kowalska**: anna.kowalska@example.com | password456  
- **Jan Nowak**: jan.nowak@example.com | password789

Każdy użytkownik ma różne budżety i przykładowe transakcje.

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

## 🧪 Testowanie

### Uruchamianie testów
```bash
# Wszystkie testy
php vendor/bin/phpunit

# Tylko testy encji
php vendor/bin/phpunit tests/Entity/

# Tylko testy DTO
php vendor/bin/phpunit tests/DTO/

# Z pokryciem kodu
php vendor/bin/phpunit --coverage-text
```

### Pokrycie testowe
- **Testy encji**: User, Budget, Transaction (49 testów)
- **Testy DTO**: Walidacja wszystkich DTO (94 testy)
- **Łącznie**: 143 testy jednostkowe

## 🚀 CI/CD

### GitHub Actions
Automatyczne testy uruchamiane przy:
- Push na branchi `master`/`main`
- Tworzeniu Pull Request
- Testy na PHP 8.1, 8.2, 8.3
- Budowanie assets
- Sprawdzanie bezpieczeństwa

### Workflow
1. Stwórz branch feature z `master`
2. Wprowadź zmiany i commituj
3. Pushuj branch i utwórz Pull Request
4. GitHub Actions automatycznie uruchamia testy
5. Sprawdź wyniki w zakładce Actions
6. Zmerguj po upewnieniu się, że testy przechodzą

## 📝 TODO / Rozszerzenia
- Kategorie transakcji
- Eksport danych CSV/Excel
- Wykresy i statystyki
- Powiadomienia o limitach
- Aplikacja mobilna
- Integracje bankowe
- Testy funkcjonalne
- Testy E2E

## 🔗 Przydatne Linki
- [Symfony Docs](https://symfony.com/doc)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)
- [Bootstrap 5](https://getbootstrap.com/)
- [Stimulus](https://stimulus.hotwired.dev/)