# Travel Request Microservice

Microsserviço em Laravel para gerenciamento de pedidos de viagem corporativa.

## Características

- API REST completa para gerenciamento de pedidos
- Autenticação JWT
- Sistema de notificações
- Validação de dados robusta
- Testes automatizados
- Containerização com Docker

## Instalação

1. Clone o repositório
2. Execute `docker-compose up -d --build`
3. Execute `docker-compose exec app php artisan migrate`
4. Execute `docker-compose exec app php artisan jwt:secret`
5. Acesse `http://localhost:8000`

## Endpoints

### Autenticação
- POST /api/auth/register
- POST /api/auth/login
- POST /api/auth/logout
- GET /api/auth/me

### Pedidos de Viagem
- GET /api/travel-requests
- POST /api/travel-requests
- GET /api/travel-requests/{id}
- PUT/PATCH /api/travel-requests/{id}
- DELETE /api/travel-requests/{id}
- PATCH /api/travel-requests/{id}/status (admin only)
- PATCH /api/travel-requests/{id}/cancel

## Uso da API

### Registro de Usuário
```bash
POST /api/auth/register
{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "12345678",
  "role": "admin"  # opcional, padrão: "user"
}
```

### Login
```bash
POST /api/auth/login
{
  "email": "joao@example.com",
  "password": "12345678"
}
```

### Criar Solicitação de Viagem
```bash
POST /api/travel-requests
Authorization: Bearer SEU_TOKEN
{
  "applicant_name": "João Silva",
  "destination": "São Paulo",
  "departure_date": "2025-08-15",
  "return_date": "2025-08-20",
  "reason": "Reunião com cliente"
}
```

## Testes

Execute `docker-compose exec app php artisan test`

## Tecnologias

- Laravel 11
- PHP 8.2
- MySQL 8.0
- Redis
- Docker
- JWT Auth (Tymon)
- PHPUnit
- Apache
