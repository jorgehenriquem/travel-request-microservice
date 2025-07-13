# Travel Request Microservice


## 🚀 Tecnologias

- **Laravel 11**
- **PHP 8.2+** 
- **Docker & Docker Compose** 
- **JWT Authentication**
- **PHPUnit**

## 📋 Pré-requisitos

- Docker
- Docker Compose
- Git

## 🛠️ Instalação

### 1. Clone o repositório


### 2. Configure as variáveis de ambiente
```bash
cp .env.example .env
```

### 3. Execute com Docker
```bash
docker-compose up -d
```

### 4. Instale as dependências
```bash
docker-compose exec app composer install
```

### 5. Execute as migrações
```bash
docker-compose exec app php artisan migrate
```

### 6. Gere a chave da aplicação
```bash
docker-compose exec app php artisan key:generate
```

### 7. Gere a chave JWT
```bash
docker-compose exec app php artisan jwt:secret
```

## 🧪 Testes

### Executar todos os testes
```bash
docker-compose exec app php artisan test
```


### Executar testes específicos
```bash
docker-compose exec app php artisan test --filter=TravelRequestApiTest
```

## 📡 Endpoints da API

### Autenticação
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `POST` | `/api/auth/register` | Registrar novo usuário |
| `POST` | `/api/auth/login` | Fazer login |
| `POST` | `/api/auth/logout` | Fazer logout |
| `GET` | `/api/auth/me` | Obter informações do usuário |

### Solicitações de Viagem
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `GET` | `/api/travel-requests` | Listar solicitações (com filtros) |
| `POST` | `/api/travel-requests` | Criar nova solicitação |
| `GET` | `/api/travel-requests/{id}` | Ver solicitação específica |
| `PATCH` | `/api/travel-requests/{id}/status` | Atualizar status da solicitação |

### Filtros Disponíveis
- `status` - Filtrar por status (requested, approved, cancelled)
- `destination` - Filtrar por destino
- `start_date` - Data inicial do período
- `end_date` - Data final do período

## 🔐 Autenticação

A API usa JWT (JSON Web Tokens) para autenticação.

### Exemplo de uso:
```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'

# Usar token em requisições
curl -X GET http://localhost:8000/api/travel-requests \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

## 📊 Funcionalidades

### Solicitações de Viagem
- ✅ Criar solicitação de viagem
- ✅ Listar solicitações com filtros
- ✅ Consultar solicitação por ID
- ✅ Atualizar status (aprovado/cancelado)
- ✅ Validações de dados
- ✅ Autorização baseada em roles

### Notificações
- ✅ Notificação automática ao aprovar solicitação
- ✅ Notificação automática ao cancelar solicitação

### Testes
- ✅ Testes de criação de solicitações
- ✅ Testes de validação
- ✅ Testes de autorização
- ✅ Testes de filtros
- ✅ Testes de notificações

## 🐳 Docker

### Comandos úteis
```bash
# Ver logs
docker-compose logs -f app

# Acessar container
docker-compose exec app bash

# Parar containers
docker-compose down

# Rebuild containers
docker-compose up -d --build
```



## 📝 Licença

Este projeto está sob a licença MIT.
