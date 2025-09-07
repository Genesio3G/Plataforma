
# Documentação API

**Objectivo**: Projeto base em Laravel 11+ utilizando PHP 8.3 e banco de dados Mysql. Estruturado para autenticação via Sanctum, sessões seguras e integração futura com regras de negócio específicas.

---

## 🚀 Requisitos

- PHP 8.3+
- Composer
- MySQL (porta padrão: 3306)

---

## ⚙️ Instalação

composer create-project laravel/laravel:^11.0 api_Inscricao_Selecao

## ▶️ Como rodar o projeto

cd api_Inscricao_Selecao

----
## ⚙️ Configuração do Ambiente `.env`

Após instalar o Laravel e acessar o diretório do projeto (`cd api_Inscricao_Selecao`), configure o arquivo `.env` com os seguintes parâmetros:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=plataforma
DB_USERNAME=root
DB_PASSWORD=root

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SANCTUM_STATEFUL_DOMAINS=

```
---
## 🔐 Instalação e Configuração do Laravel Sanctum

O Laravel Sanctum fornece autenticação simples para SPAs, aplicativos mobile e APIs baseadas em tokens.

### 1️⃣ Instalar o pacote Sanctum
```bash
composer require laravel/sanctum

php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

# 🔐 Autenticação via API com Laravel Sanctum

Este projeto utiliza [Laravel Sanctum](https://laravel.com/docs/sanctum) para autenticação de APIs, com uma configuração personalizada para autenticar usuários do tipo **Candidato**.

---

## ⚙️ Configuração de Autenticação (`config/auth.php`)

```php
'guards' => [
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'candidatos',
    ],
],

'providers' => [
    'candidatos' => [
        'driver' => 'eloquent',
        'model'  => App\Models\Candidato::class,
    ],
],

```
## 📦 Modelos e Relacionamentos

Este projeto utiliza três modelos principais para representar o fluxo de inscrição de candidatos em programas:

| Modelo      | Relacionamentos                              |
|-------------|----------------------------------------------|
| Candidato   | tem muitas **Candidaturas**                  |
| Programa    | tem muitas **Candidaturas**                  |
| Candidatura | pertence a **Candidato** e a **Programa**    |

---

### 🔗 Relacionamentos entre os modelos

- **Candidato** pode se inscrever em vários programas → `(Candidatura)`
- **Programa** pode receber várias inscrições → `(Candidatura)`
- **Candidatura** é a ligação entre um candidato e um programa → `(Candidato)` e `(Programa)`

---

## 🛠️ Criação das Tabelas com Migrations

### 🧍 Tabela `candidatos`

```bash
php artisan make:migration create_candidatos_table
Schema::create('candidatos', function (Blueprint $table) {
    $table->id();
    $table->string('nome');
    $table->string('email')->unique();
    $table->string('password');
    $table->date('data_nascimento');
    $table->timestamps();
});

php artisan make:migration create_programas_table
Schema::create('programas', function (Blueprint $table) {
    $table->id();
    $table->string('nome');
    $table->text('descricao')->nullable();
    $table->date('data_inicio');
    $table->date('data_final');
    $table->enum('estado', ['ativo', 'inativo']);
    $table->timestamps();
});

php artisan make:migration create_candidaturas_table
Schema::create('candidaturas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('candidato_id')->constrained()->onDelete('cascade');
    $table->foreignId('programa_id')->constrained()->onDelete('cascade');
    $table->enum('status', ['pendente', 'aprovado', 'rejeitado'])->default('pendente');
    $table->timestamps();
});

```
## 🧩 Modelos Eloquent

Este projeto utiliza três modelos principais para representar o fluxo de candidaturas a programas:

---

### 📌 `App\Models\Candidato`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Candidato extends Model
{
    use HasApiTokens, HasFactory, Notifiable, \Illuminate\Auth\Authenticatable;

    protected $fillable = ['nome', 'email', 'password', 'data_nascimento'];

    public function candidaturas()
    {
        return $this->hasMany(Candidatura::class);
    }
}
```
### 📌 `App\Models\Candidatura`
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidatura extends Model
{
    protected $fillable = ['candidato_id', 'programa_id', 'status'];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class);
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }
}
```
### 📌 `App\Models\Programa`
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    protected $fillable = ['nome', 'descricao', 'data_inicio', 'data_final', 'estado'];

    public function candidaturas()
    {
        return $this->hasMany(Candidatura::class);
    }

    public function estaDisponivel()
    {
        return $this->estado === 'ativo'
            && now()->between($this->data_inicio, $this->data_final);
    }
}

```
## 🌱 Seeder: ProgramaSeeder

O seeder `ProgramaSeeder` é responsável por popular a tabela `programas` com dados iniciais para testes e desenvolvimento. Ele insere dois registros distintos que representam diferentes estados e períodos de disponibilidade de programas.

### 📄 Código do Seeder

```php
class ProgramaSeeder extends Seeder
{
    public function run(): void
    {
        Programa::create([
            'nome' => 'Programa A',
            'descricao' => 'Primeira oportunidade de teste',
            'data_inicio' => now()->subDays(5),
            'data_final' => now()->addDays(10),
            'estado' => 'ativo'
        ]);

        Programa::create([
            'nome' => 'Programa B',
            'descricao' => 'Programa expirado para validação de regras',
            'data_inicio' => now()->subDays(20),
            'data_final' => now()->subDays(5),
            'estado' => 'inativo'
        ]);
    }
}

```

## 🗃️ Tabelas Adicionais Criadas pelo Laravel

Durante a configuração da autenticação com Laravel Sanctum e sessões de login, duas tabelas adicionais foram criadas automaticamente via migrations:

### 🔐 `personal_access_tokens`

- Criada pelo Laravel Sanctum
- Armazena os tokens de acesso gerados para autenticação via API
- Cada token está vinculado a um usuário (neste caso, um `Candidato`)
- Campos principais:
  - `tokenable_id` e `tokenable_type`: identificam o modelo autenticado
  - `token`: o valor do token
  - `abilities`: permissões associadas ao token
  - `last_used_at`: data da última utilização

### 🧑‍💻 `sessions`

- Criada pelo Laravel para gerenciar sessões de login (usada em autenticação baseada em sessão)
- Armazena informações como ID da sessão, IP, agente de usuário e tempo de expiração
- Útil para aplicações que usam autenticação via navegador ou SPA com cookies

---

🛑 Essas tabelas são essenciais para garantir segurança e rastreabilidade na autenticação de usuários via API e sessão. Certifique-se de executar as migrations corretamente:

---
## 🧭 Controllers da Aplicação

A aplicação utiliza três controllers principais para gerenciar autenticação, dados dos candidatos e programas disponíveis. Cada controller é responsável por uma parte essencial do fluxo de uso da plataforma.

---

### 🔐 `AuthController`

Responsável por gerenciar o processo de autenticação dos candidatos via API.

#### 📄 Métodos:

- `register(Request $request)`  
  Registra um novo candidato, valida os dados e gera um token de acesso via Laravel Sanctum.

- `login(Request $request)`  
  Autentica um candidato com email e senha. Se as credenciais forem válidas, retorna um token de acesso.

- `logout(Request $request)`  
  Revoga todos os tokens do candidato autenticado, encerrando a sessão.

#### ✅ Importância:
Este controller é essencial para garantir que apenas usuários autenticados possam acessar rotas protegidas. Ele implementa o fluxo completo de login, registro e logout com segurança.

---

### 👤 `CandidatoController`

Gerencia os dados do candidato autenticado e permite o registro via outro endpoint.

#### 📄 Métodos:

- `me()`  
  Retorna os dados do candidato atualmente autenticado.

- `register(Request $request)`  
  Registra um novo candidato (sem geração de token). Pode ser usado em contextos onde o login é feito separadamente.

#### ✅ Importância:
Permite que o candidato consulte seus próprios dados e oferece uma alternativa de registro. Útil para aplicações que separam o fluxo de autenticação e perfil.

---

### 📚 `ProgramaController`

Gerencia os programas disponíveis para candidatura.

#### 📄 Métodos:

- `index()`  
  Retorna todos os programas ativos e dentro do período de inscrição.

- `store(Request $request)`  
  Cria um novo programa com validação de dados como datas e estado.

#### ✅ Importância:
Este controller é fundamental para listar oportunidades disponíveis e cadastrar novos programas. O método `index()` aplica regras de negócio para garantir que apenas programas válidos sejam exibidos.

---

## 🧭 Controllers da Aplicação

A seguir estão os controllers principais utilizados na aplicação, com suas respectivas funções e código completo:

---

### 🔐 `AuthController`

Responsável pela autenticação via API usando Laravel Sanctum.

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Candidato;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nome' => 'required|string',
            'email' => 'required|email|unique:candidatos',
            'data_nascimento' => 'nullable|date',
            'password' => 'required|confirmed|min:6'
        ]);

        $candidato = Candidato::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'data_nascimento' => $request->data_nascimento
        ]);

        $token = $candidato->createToken('plataforma-candidaturas')->plainTextToken;

        return response()->json([
            'mensagem' => 'Registro realizado com sucesso!',
            'token' => $token,
            'candidato' => $candidato
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $candidato = Candidato::where('email', $request->email)->first();

        if (! $candidato || ! Hash::check($request->password, $candidato->password)) {
            return response()->json(['erro' => 'Credenciais inválidas'], 401);
        }

        $token = $candidato->createToken('plataforma-candidaturas')->plainTextToken;

        return response()->json([
            'token' => $token,
            'candidato' => $candidato
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['mensagem' => 'Logout realizado com sucesso']);
    }
}
```
---

### 🔐 `CandidatoController`

Gerencia os dados do candidato autenticado e permite registro.

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Candidato;
use Illuminate\Support\Facades\Hash;

class CandidatoController extends Controller
{
    public function me()
    {
        $candidato = Candidato::find(Auth::id());
        return response()->json($candidato);
    }

    public function register(Request $request)
    {
        $request->validate([
            'nome' => 'required|string',
            'email' => 'required|email|unique:candidatos',
            'password' => 'required|confirmed|min:6',
            'data_nascimento' => 'nullable|date'
        ]);

        $candidato = Candidato::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'data_nascimento' => $request->data_nascimento
        ]);

        return response()->json($candidato, 201);
    }
}
```
---

### 🔐 `ProgramaController`

Gerencia os programas disponíveis para candidatura.

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Programa;

class ProgramaController extends Controller
{
    public function index()
    {
        $programas = Programa::where('estado', 'ativo')
            ->whereDate('data_inicio', '<=', now())
            ->whereDate('data_final', '>=', now())
            ->get();

        return response()->json($programas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string',
            'descricao' => 'nullable|string',
            'data_inicio' => 'required|date',
            'data_final' => 'required|date|after_or_equal:data_inicio',
            'estado' => 'required|in:ativo,inativo'
        ]);

        $programa = Programa::create($request->all());

        return response()->json($programa, 201);
    }
}
```
---
## 🚦 Rotas da API

A aplicação define um conjunto de rotas organizadas em dois grupos principais: **rotas públicas** (sem autenticação) e **rotas protegidas** (requerem autenticação via Laravel Sanctum).

---

### 🔓 Rotas Públicas

Essas rotas estão acessíveis sem autenticação e são usadas para registrar e autenticar candidatos.

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidatoController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\CandidaturaController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    // 👤 Candidato autenticado
    Route::get('/candidato', [CandidatoController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // 📋 Programas
    Route::get('/programas', [ProgramaController::class, 'index']);
    Route::post('/programas', [ProgramaController::class, 'store']);

    // 📝 Candidaturas
    Route::get('/candidaturas', [CandidaturaController::class, 'index']);
    Route::post('/candidaturas', [CandidaturaController::class, 'store']);
});

👤 Candidato
GET /candidato: Retorna os dados do candidato autenticado.

POST /logout: Revoga todos os tokens do candidato, encerrando a sessão.

📋 Programas
GET /programas: Lista todos os programas ativos e disponíveis para candidatura.

POST /programas: Cria um novo programa (requer permissão adequada).

📝 Candidaturas
GET /candidaturas: Lista todas as candidaturas do candidato autenticado.

POST /candidaturas: Cria uma nova candidatura para um programa.
```
---
## 📬 Postman Collection

Para facilitar os testes da API, criamos uma coleção do Postman com todas as rotas organizadas e prontas para uso.

---

### 📂 Estrutura da Coleção

A coleção está organizada em três grupos principais:

#### 🔐 Autenticação

- `POST /register` — Registra um novo candidato
- `POST /login` — Autentica e retorna token
- `POST /logout` — Revoga o token do candidato autenticado

#### 👤 Candidato

- `GET /candidato` — Retorna os dados do candidato autenticado

#### 📋 Programas

- `GET /programas` — Lista programas ativos e disponíveis
- `POST /programas` — Cria um novo programa

#### 📝 Candidaturas

- `GET /candidaturas` — Lista candidaturas do candidato autenticado
- `POST /candidaturas` — Cria uma nova candidatura

---

### 🛠️ Como usar

1. Instale o [Postman](https://www.postman.com/downloads/)
2. Importe a coleção usando o link acima ou o arquivo `.json`
3. Configure a variável `{{base_url}}` com a URL da sua API (ex: `http://localhost:8000/api`)
4. Após login, copie o token de acesso e adicione no header das requisições protegidas:

```http
Authorization: Bearer SEU_TOKEN_AQUI
```
---
## 🧪 Testes de Endpoints da API

Abaixo estão os testes realizados para cada endpoint da API, com exemplos de requisições e os resultados esperados.

---

### 🔐 Autenticação

#### ✅ `POST /register`

**Descrição**: Registra um novo candidato.

**Requisição**:

```json
POST /api/register
Content-Type: application/json
JSON
{
  "nome": "Moises Borracha",
  "email": "moises.borracha@positiva.co.ao",
  "password": "senha123",
  "password_confirmation": "senha123",
  "data_nascimento": "1998-07-10"
} ou
{
  "nome": "Ricardo Sá",
  "email": "ricardo.sa@positiva.co.ao",
  "password": "senha111",
  "password_confirmation": "senha111",
  "data_nascimento": "1998-07-09"
}
```
#### ✅ Resultado do Registro de Candidato

Após realizar uma requisição `POST /api/register`, o seguinte JSON foi retornado com sucesso:

```json

{
  "mensagem": "Registro realizado com sucesso!",
  "token": "10|5iRMG1Tr4rR3VdWQWIMhNK3PVOnpkMDAQvb6yEGp693d8220",
  "candidato": {
    "id": 12,
    "nome": "Moises Borracha",
    "email": "moises.borracha@positiva.co.ao",
    "password": "$2y$12$Ln734lok7LLoPeaYf6EuA.YOSbcL16JPvz4P6lDbAFGb5ftLSTVmu",
    "data_nascimento": "1998-07-10",
    "created_at": "2025-09-07T10:40:16.000000Z",
    "updated_at": "2025-09-07T10:40:16.000000Z"
  }
}
```
### 🔐 Teste de Login — `POST /login`

Após o registro bem-sucedido do candidato **Moises Borracha**, realizamos o teste de login via Postman utilizando o endpoint `POST /api/login`.

---

### 📤 Requisição

```http
POST /api/login
Content-Type: application/json
Authorization:Bearer 10|5iRMG1Tr4rR3VdWQWIMhNK3PVOnpkMDAQvb6yEGp693d8220

JSON
{
  "email": "moises.borracha@positiva.co.ao",
  "password": "senha123"
}

```
-----
#### 🔐 Resultado do Login — `POST /api/login`

Após realizar o login com sucesso, a API retornou os seguintes dados:

```json
{
  "token": "11|RzHnFEBcRVlHXGxEZFrAIFWAO7MyrZWgHwbT0yP096163e56",
  "candidato": {
    "id": 12,
    "nome": "Moises Borracha",
    "email": "moises.borracha@positiva.co.ao",
    "password": "$2y$12$Ln734lok7LLoPeaYf6EuA.YOSbcL16JPvz4P6lDbAFGb5ftLSTVmu",
    "data_nascimento": "1998-07-10",
    "created_at": "2025-09-07T10:40:16.000000Z",
    "updated_at": "2025-09-07T10:40:16.000000Z"
  }
}
```
#### 📝 Teste de Candidatura — `POST /api/candidaturas`

Este teste verifica se um candidato autenticado consegue se inscrever em um programa ativo.

---

### 📤 Requisição

```http
POST /api/candidaturas
Authorization: Bearer 11|RzHnFEBcRVlHXGxEZFrAIFWAO7MyrZWgHwbT0yP096163e56
Content-Type: application/json

JSON
{
  "programa_id": 1,
  "status": "pendente"
}

```
#### 📝 Resultado da Candidatura — `POST /api/candidaturas`

Após o envio da candidatura, a API retornou a seguinte resposta confirmando o sucesso da operação:

```json
{
  "sucesso": true,
  "mensagem": "Inscrição realizada com sucesso.",
  "candidatura": {
    "id": 2,
    "candidato_id": 12,
    "programa_id": 1,
    "status": "pendente",
    "created_at": "2025-09-07T10:51:36.000000Z",
    "updated_at": "2025-09-07T10:51:36.000000Z"
  }
}
```
---
#### 📋 Teste de Criação de Programa — `POST /api/programas`

Este teste verifica se um usuário autenticado consegue cadastrar um novo programa disponível para candidatura.

---

### 📤 Requisição

```http
POST /api/programas
Authorization: Bearer 11|RzHnFEBcRVlHXGxEZFrAIFWAO7MyrZWgHwbT0yP096163e56
Content-Type: application/json

JSON
{
  "nome": "Programa C",
  "descricao": "Programa de inovação para jovens talentos",
  "data_inicio": "2025-09-10",
  "data_final": "2025-09-30",
  "estado": "ativo"
}
```
---
#### 📋 Resultado da Criação de Programa — `POST /api/programas`

Após o envio da requisição para cadastrar um novo programa, a API retornou os seguintes dados confirmando o sucesso da operação:

```json
{
  "id": 4,
  "nome": "Programa C",
  "descricao": "Programa de inovação para jovens talentos",
  "data_inicio": "2025-09-10",
  "data_final": "2025-09-30",
  "estado": "ativo",
  "created_at": "2025-09-07T11:21:49.000000Z",
  "updated_at": "2025-09-07T11:21:49.000000Z"
}
```
---
### 📝 Listar Candidaturas — `GET /api/candidaturas`

Este endpoint retorna todas as candidaturas associadas ao candidato autenticado, permitindo que ele acompanhe suas inscrições em programas.



```JSON
GET /api/candidaturas
Authorization: Bearer 11|RzHnFEBcRVlHXGxEZFrAIFWAO7MyrZWgHwbT0yP096163e56

[
  {
    "id": 2,
    "candidato_id": 12,
    "programa_id": 1,
    "status": "pendente",
    "created_at": "2025-09-07T10:51:36.000000Z",
    "updated_at": "2025-09-07T10:51:36.000000Z"
  },
  {
    "id": 3,
    "candidato_id": 12,
    "programa_id": 4,
    "status": "pendente",
    "created_at": "2025-09-07T12:28:00.000000Z",
    "updated_at": "2025-09-07T12:28:00.000000Z"
  }
]
```
---
## 📝 Candidaturas Realizadas

Abaixo estão as candidaturas feitas pelo candidato com ID `12`, incluindo os detalhes dos programas associados:

---

### 📌 Candidatura #2

- **ID da candidatura**: 2  
- **Data de submissão**: 2025-09-07 11:51:36  
- **Status**: pendente  

#### 🎓 Programa Associado

- **ID do programa**: 1  
- **Nome**: Programa A  
- **Descrição**: Primeira oportunidade de teste  
- **Período de inscrição**: 01/09/2025 até 16/09/2025  
- **Estado**: ativo  

---

### 📌 Candidatura #3

- **ID da candidatura**: 3  
- **Data de submissão**: 2025-09-07 12:29:54  
- **Status**: pendente  

#### 🎓 Programa Associado

- **ID do programa**: 4  
- **Nome**: Programa C  
- **Descrição**: Programa de inovação para jovens talentos  
- **Período de inscrição**: 07/09/2025 até 30/09/2025  
- **Estado**: ativo  

---

### ✅ Observações

- Ambas as candidaturas estão com status **pendente**, aguardando avaliação.
- Os programas associados estão **ativos** e dentro do período de inscrição.
- As datas de submissão indicam que as inscrições foram feitas com sucesso e dentro do prazo.

---

---

### 📤 Requisição

```http
GET /api/candidaturas
Authorization: Bearer 11|RzHnFEBcRVlHXGxEZFrAIFWAO7MyrZWgHwbT0yP096163e56

---
#### ❌ Teste de Candidatura Expirada — `POST /api/candidaturas` com `programa_id: 2`

Este teste simula a tentativa de inscrição em um programa cujo período de inscrição já terminou.

---

### 📤 Requisição

```http
POST /api/candidaturas
Authorization: Bearer 11|RzHnFEBcRVlHXGxEZFrAIFWAO7MyrZWgHwbT0yP096163e56
Content-Type: application/json
JSON
{
  "programa_id": 2,
  "status": "pendente"
}
🟩 Resposta Esperada
{
  "sucesso": false,
  "mensagem": "Programa indisponível para inscrição."
}



