## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

# Desafio Técnico – Mini CRM de Contatos (DDD & TDD)

Este desafio tem como objetivo avaliar suas habilidades avançadas em engenharia de software, design de arquitetura e fluência no ecossistema Laravel. 

Você deverá construir uma pequena API REST para gerenciar contatos e acompanhar, em tempo real, a evolução do **score** (pontuação) desses contatos quando um processamento assíncrono for executado.

**O foco principal não é apenas entregar a funcionalidade, mas como você estrutura o código.** Esperamos ver a aplicação de **SOLID**, **Domain-Driven Design (DDD)** (ou Arquitetura Hexagonal/Clean Architecture) e **Test-Driven Development (TDD)**.

---

## 1. Escopo Funcional

### Modelo `Contact`

| Campo          | Tipo               | Regras / Default                           |
|----------------|--------------------|--------------------------------------------|
| `id`           | bigint / PK        | auto-increment                             |
| `name`         | string             | obrigatório                                |
| `email`        | string único       | obrigatório \| formato e-mail              |
| `phone`        | string             | obrigatório                                |
| `score`        | integer            | default **0**                              |
| `status`       | string (Enum)      | `pending`, `processing`, `active`, `failed`|
| `processed_at` | timestamp nullable | preenchido após processamento do score     |
| Timestamps     | `created_at`, `updated_at`, `deleted_at` (soft delete)            |

### Endpoints CRUD

| Método | Rota                      | Ação                     |
|--------|---------------------------|--------------------------|
| POST   | `/api/contacts`           | Criar contato (inicia como `pending` e score 0) |
| GET    | `/api/contacts`           | Listar contatos (com paginação) |
| GET    | `/api/contacts/{id}`      | Mostrar contato          |
| PUT    | `/api/contacts/{id}`      | Atualizar contato        |
| DELETE | `/api/contacts/{id}`      | Excluir contato (soft)   |

### Fluxo de Processamento de Score (Regras de Negócio)

1. **Endpoint de Gatilho**
   `POST /api/contacts/{id}/process-score`

2. **Processamento Assíncrono (Job)**
   A rota deve enfileirar o processamento. O Job mudará o status do contato para `processing`.

3. **Cálculo do Score (Domínio)**
   O score não é aleatório. Ele deve ser calculado por um **Domain Service** ou **Use Case** isolado, baseado nas seguintes regras de negócio (utilize padrões como *Strategy* para permitir fácil extensão futura):
   - **E-mail**: Domínios corporativos (não gmail, hotmail, yahoo) ganham +20 pontos. E-mails terminados em `.br` ganham +10 pontos.
   - **Nome**: Nomes completos (com mais de uma palavra) ganham +10 pontos.
   - **Telefone**: Se possuir código de área (DDD) válido do estado de São Paulo (11 a 19), ganha +20 pontos. Se for de outros estados, +10 pontos.
   - *(A carga de cálculo pode ser simulada com um `sleep(1)` ou `sleep(2)` para emular demora e validar o fluxo assíncrono).*

4. **Finalização**
   - O status do contato passa para `active` (ou `failed` caso ocorra alguma falha na regra).
   - O score calculado é salvo e a data em `processed_at` é preenchida.
   - Um evento de domínio `ContactScoreProcessed` é disparado.

5. **Reação ao Evento (Listeners & WebSockets)**
   - **Log**: Um Listener grava no arquivo `storage/logs/contact.log` (ID, email, novo score, status).
   - **Broadcast via Reverb**: A atualização do contato deve ser enviada para o frontend via websockets (canal `contacts.{id}`).

---

## 2. Requisitos Arquiteturais e Técnicos

Esperamos que sua solução se afaste do padrão clássico MVC "fat-controller / fat-model" do Laravel e utilize conceitos de **DDD / Arquitetura Limpa**.

| Área | Requisito Esperado |
| :--- | :--- |
| **Domain Layer** | Suas regras de negócio (ex: cálculo do score, mudança de status) devem ser **agnósticas ao framework**. Utilize entidades ricas e *Value Objects* (ex: para Email, Phone, Status). |
| **Application Layer** | Implemente *Use Cases* (ou *Actions*) para orquestrar as operações (ex: `CreateContactUseCase`, `CalculateScoreUseCase`). |
| **Infrastructure Layer** | Aqui entram os recursos do Laravel: Controllers, Repositórios (Eloquent), Jobs, Events, Listeners e Requests. |
| **Inversão de Dependência** | Utilize Interfaces para acoplar os *Use Cases* à infraestrutura (Repositories). Configure as dependências no *Service Container* do Laravel. |
| **Validação e Saída** | Use **Form Requests** para validação de entrada (HTTP) e **API Resources** para padronizar o JSON de saída. |
| **Queue & Broadcast** | Use **Redis** para a fila e **Laravel Reverb** para o WebSocket. Inclua um exemplo simples (HTML/JS) no `README` de como escutar o canal. |

### Exemplo de WebSocket (HTML/JS)

Você pode encontrar uma interface completa para monitorar eventos em tempo real no arquivo `resources/views/welcome.blade.php`.
Abaixo está um exemplo básico em JS de como se inscrever em um canal específico para um contato:

```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

<script>
    const echo = new window.Echo({
        broadcaster: 'pusher',
        key: 'app-key', // Substitua pela sua PUSHER_APP_KEY
        wsHost: '127.0.0.1',
        wsPort: 6001,
        forceTLS: false,
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
    });

    // Substitua o {id} pelo ID do contato desejado
    const contactId = 1;
    echo.channel(`contacts.${contactId}`)
        .listen('.ContactScoreProcessed', (contact) => {
            console.log('Score processado!', contact);
            alert(`O contato ${contact.name} agora possui score ${contact.score} e status ${contact.status}.`);
        });
</script>
```

---

## 3. Critérios de Avaliação

Avaliaremos severamente a qualidade do seu código, não apenas se a API "funciona".

| Peso | Critério                                                                                         |
|------|--------------------------------------------------------------------------------------------------|
| ⭐⭐⭐  | **Arquitetura & SOLID**: Separação clara entre Domínio, Aplicação e Infraestrutura. Correto uso de injeção de dependência e segregação de responsabilidades. |
| ⭐⭐⭐  | **Testes (TDD)**: Seu histórico de commits deve preferencialmente demonstrar o ciclo red-green-refactor. Exigimos **Testes de Unidade** para a camada de Domínio/Aplicação (mockando infraestrutura) e **Testes de Integração (Feature)** para os endpoints e integração com banco/filas. |
| ⭐⭐   | **Design de Código (Design Patterns)**: Uso adequado de padrões como *Strategy*, *Value Objects* e *Repository Pattern*. Entidades anêmicas custarão pontos. |
| ⭐⭐   | **Fluência no Laravel**: Uso correto de Form Requests, API Resources, Jobs, Events/Listeners, Reverb e Observers (ex: `saving` para normalizar o formato do telefone). |
| ⭐    | **Documentação & Setup**: Clareza no README.md ensinando a subir o ambiente (Laravel Sail ou Docker Compose customizado), rodar migrations, filas, websockets e rodar os testes. |

---

## 4. Instruções de Entrega

1. **Faça um fork/clone** deste repositório e inicie o desenvolvimento.
2. Certifique-se de que os testes podem ser executados facilmente por quem for avaliar o teste (ex: `php artisan test`).
3. Faça *commits* semânticos e granulares que demonstrem sua linha de raciocínio e a adoção do TDD.
4. Quando finalizar, publique em um repositório seu (pode ser privado, basta nos dar acesso) e nos envie o link.
5. **Prazo de entrega sugerido**: 7 dias. Foque na qualidade da arquitetura e dos testes, mesmo que o escopo funcional não esteja 100% polido.

Boa sorte 🚀
