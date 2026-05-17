# Mini CRM de Contatos (DDD & TDD) - Solução do Desafio

A infraestrutura foi totalmente **dockerizada** para garantir que a aplicação rode perfeitamente em qualquer ambiente. A arquitetura inclui:
- **App (Laravel)**: API e front-end de monitoramento (porta `8000`).
- **PostgreSQL**: Banco de dados relacional (porta `5432`).
- **Redis**: Armazenamento em cache e filas em memória (porta `6379`).
- **Queue Worker**: Container dedicado rodando `queue:work` para processamento assíncrono.
- **Soketi**: Servidor de WebSockets compatível com o Pusher (porta `6001`).

---

## 🚀 Guia Rápido de Instalação e Uso

### 1. Requisitos
- **Docker** e **Docker Compose** instalados na máquina.
- As portas `8000`, `5432`, `6379`, e `6001` devem estar livres.

### 2. Clonando o Repositório
Faça o clone do projeto e entre na pasta raiz:
```bash
git clone https://github.com/Guilherme0112/laravel-mini-crm-contatos.git
cd laravel-mini-crm-contatos
```

### 3. Subindo o Ambiente (Docker Compose)
A aplicação está configurada para subir tudo de forma automática, incluindo as dependências e a compilação.
Na raiz do projeto, execute:

```bash
docker-compose up -d --build
```
> **Nota:** As *migrations* são executadas automaticamente assim que o banco de dados (Postgres) fica saudável (`healthcheck`) e o container da aplicação inicializa. Não é necessário rodá-las manualmente.

### 4. Popular o Banco de Dados (Opcional)
Se desejar gerar dados falsos (seeders) para testar a aplicação, rode:
```bash
docker-compose exec app php artisan db:seed
```

### 5. Filas e WebSockets (Em Tempo Real)
O processamento das filas (`queue`) e o servidor de WebSockets (`soketi`) já iniciam automaticamente com o `docker-compose`. 
- O **Queue Worker** escuta a fila via Redis e processa os Jobs.
- Você pode acessar a interface visual de **Monitoramento em Tempo Real** no navegador:  
  👉 **[http://localhost:8000/](http://localhost:8000/)**

### 6. Como rodar os Testes
Foi desenvolvida uma suíte robusta contemplando **Testes de Unidade** (Camada de Aplicação e Domínio) e **Testes de Integração/Feature** (Endpoints, Bancos e Filas).
Para executar toda a suíte de testes de dentro do container:

```bash
docker-compose exec app php artisan test
```
